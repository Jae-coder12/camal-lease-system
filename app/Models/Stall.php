<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Stall extends Model
{
    protected $fillable = ['stall_number', 'location', 'size_sqm', 'monthly_rate', 'description'];

    public function owner() { return $this->belongsTo(Registrant::class, 'registrant_id'); }
    public function leases() { return $this->hasMany(Lease::class); }
    public function activeLease() { return $this->hasOne(Lease::class)->where('status', 'active'); }
    public function applications() { return $this->hasMany(Application::class); }

    /**
     * Single place where ownership changes. Ends the old lease, opens a new one
     * and keeps stall status/owner in sync automatically.
     */
    public function assignOwner(?int $registrantId): void
    {
        if ($this->registrant_id === $registrantId) return;

        DB::transaction(function () use ($registrantId) {
            $this->leases()->where('status', 'active')
                ->update(['status' => 'ended', 'end_date' => now()->toDateString()]);

            if ($registrantId) {
                Lease::create([
                    'stall_id' => $this->id,
                    'registrant_id' => $registrantId,
                    'start_date' => now()->toDateString(),
                    'end_date' => now()->addYear()->toDateString(),
                    'monthly_rate' => $this->monthly_rate,
                    'status' => 'active',
                ]);
                $this->forceFill(['registrant_id' => $registrantId, 'status' => 'occupied'])->save();
            } else {
                $this->forceFill(['registrant_id' => null, 'status' => 'available'])->save();
            }
        });
    }
}
