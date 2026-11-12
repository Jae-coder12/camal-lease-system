<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class Application extends Model
{
    protected $fillable = [
        'registrant_id', 'stall_id', 'purpose', 'status', 'remarks', 'reviewed_at', 'reviewed_by', 'lease_id',
    ];
    protected $casts = ['reviewed_at' => 'datetime'];

    protected static function booted(): void
    {
        static::creating(function (Application $application) {
            $application->status ??= 'pending';
        });

        static::created(function (Application $application) {
            $application->statusHistories()->create([
                'to_status' => $application->status,
                'remarks' => $application->remarks,
                'acted_by' => auth()->id(),
            ]);
        });
    }

    public function registrant(): BelongsTo { return $this->belongsTo(Registrant::class); }
    public function stall(): BelongsTo { return $this->belongsTo(Stall::class); }
    public function reviewer(): BelongsTo { return $this->belongsTo(User::class, 'reviewed_by'); }
    public function lease(): BelongsTo { return $this->belongsTo(Lease::class); }
    public function statusHistories(): HasMany { return $this->hasMany(ApplicationStatusHistory::class)->latest(); }

    public function transitionTo(string $status, ?string $remarks = null, ?int $reviewerId = null): void
    {
        $allowedTransitions = [
            'pending' => ['under_review', 'approved', 'rejected', 'cancelled'],
            'under_review' => ['approved', 'rejected', 'cancelled'],
            'approved' => ['completed'],
            'rejected' => [],
            'cancelled' => [],
            'completed' => [],
        ];

        if (!in_array($status, $allowedTransitions[$this->status] ?? [], true)) {
            throw ValidationException::withMessages([
                'application' => 'This application has already been processed or cannot make that status transition.',
            ]);
        }

        DB::transaction(function () use ($status, $remarks, $reviewerId) {
            $previous = $this->status;
            $this->update([
                'status' => $status,
                'remarks' => $remarks,
                'reviewed_at' => $reviewerId ? now() : $this->reviewed_at,
                'reviewed_by' => $reviewerId,
            ]);

            $this->statusHistories()->create([
                'from_status' => $previous,
                'to_status' => $status,
                'remarks' => $remarks,
                'acted_by' => auth()->id(),
            ]);
        });
    }
}
