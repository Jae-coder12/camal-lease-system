<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Registrant extends Model
{
    protected $fillable = ['user_id', 'control_number', 'name', 'status', 'citizenship', 'birthdate', 'address', 'contact_number'];
    protected $casts = ['birthdate' => 'date'];

    protected static function booted(): void
    {
        static::creating(function (Registrant $r) {
            if (!$r->control_number) $r->control_number = self::nextControlNumber();
        });
        // Free the stalls when a registrant is deleted
        static::deleting(function (Registrant $r) {
            $r->stalls->each(fn (Stall $s) => $s->assignOwner(null));
        });
    }

    /** 2026-001, 2026-002 ... continues for every registered user (resets each year). */
    public static function nextControlNumber(): string
    {
        $year = now()->year;
        $last = self::where('control_number', 'like', "$year-%")
            ->orderByRaw('CAST(SUBSTRING(control_number, 6) AS UNSIGNED) DESC')
            ->lockForUpdate()->value('control_number');
        $n = $last ? ((int) substr($last, 5)) + 1 : 1;
        return sprintf('%d-%03d', $year, $n);
    }

    public function getAgeAttribute(): ?int { return $this->birthdate?->age; }

    public function user() { return $this->belongsTo(User::class); }
    public function stalls() { return $this->hasMany(Stall::class); }
    public function applications() { return $this->hasMany(Application::class); }
    public function leases() { return $this->hasMany(Lease::class); }
}
