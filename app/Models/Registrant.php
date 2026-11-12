<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;

class Registrant extends Model
{
    protected $fillable = ['user_id', 'control_number', 'name', 'status', 'citizenship', 'birthdate', 'address', 'contact_number'];
    protected $casts = ['birthdate' => 'date'];

    protected static function booted(): void
    {
        static::creating(function (Registrant $r) {
            if (!$r->control_number) $r->control_number = self::nextControlNumber();
        });
    }

    public static function nextControlNumber(): string
    {
        $year = (int) now()->format('Y');

        return DB::transaction(function () use ($year) {
            DB::table('control_number_sequences')->insertOrIgnore([
                'year' => $year,
                'last_number' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $sequence = DB::table('control_number_sequences')
                ->where('year', $year)->lockForUpdate()->first();
            $next = $sequence->last_number + 1;
            DB::table('control_number_sequences')->where('year', $year)->update([
                'last_number' => $next,
                'updated_at' => now(),
            ]);

            return sprintf('%d-%03d', $year, $next);
        }, 5);
    }

    public function getAgeAttribute(): ?int { return $this->birthdate?->age; }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function stalls(): HasMany { return $this->hasMany(Stall::class); }
    public function applications(): HasMany { return $this->hasMany(Application::class); }
    public function leases(): HasMany { return $this->hasMany(Lease::class); }
}
