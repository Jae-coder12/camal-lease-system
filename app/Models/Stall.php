<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;

class Stall extends Model
{
    protected $fillable = [
        'building_id', 'stall_number', 'location', 'size_sqm', 'monthly_rate',
        'rental_rate', 'payment_frequency', 'stall_type', 'shared_group', 'shared_capacity',
        'source_occupant_name', 'needs_verification', 'source_notes', 'display_order', 'description', 'status',
    ];

    protected function casts(): array
    {
        return ['needs_verification' => 'boolean', 'rental_rate' => 'decimal:2'];
    }

    public function building(): BelongsTo { return $this->belongsTo(Building::class); }
    public function owner(): BelongsTo { return $this->belongsTo(Registrant::class, 'registrant_id'); }
    public function leases(): HasMany { return $this->hasMany(Lease::class); }
    public function activeLease(): HasOne { return $this->hasOne(Lease::class)->where('status', 'active'); }
    public function applications(): HasMany { return $this->hasMany(Application::class); }
    public function occupants(): BelongsToMany
    {
        return $this->belongsToMany(Registrant::class, 'stall_occupants')
            ->withPivot(['lease_id', 'start_date', 'end_date'])->withTimestamps();
    }

    public function occupiedCapacity(): int
    {
        $leaseOccupants = $this->leases()->where('status', 'active')
            ->where(fn ($query) => $query->whereNull('end_date')->orWhereDate('end_date', '>=', today()))
            ->count();
        $hasLeaseHistory = $this->leases()->exists();

        return $leaseOccupants + (int) (bool) $this->source_occupant_name
            + (int) ($this->registrant_id && !$hasLeaseHistory && !$this->source_occupant_name);
    }

    public function remainingCapacity(): int
    {
        return max(0, $this->shared_capacity - $this->occupiedCapacity()
            - $this->applications()->where('status', 'pending')->count());
    }

    public function getRateAttribute(): string
    {
        return $this->payment_frequency === 'daily'
            ? (string) ($this->rental_rate ?? 0)
            : (string) ($this->rental_rate ?? $this->monthly_rate);
    }

    public function getOccupantNameAttribute(): ?string
    {
        if ($this->shared_capacity > 1) {
            $names = array_filter(array_merge(
                [$this->source_occupant_name],
                $this->occupants()->wherePivotNull('end_date')->pluck('name')->all(),
            ));

            return $names ? implode(', ', array_unique($names)) : null;
        }

        return $this->owner?->name ?? $this->source_occupant_name;
    }

    public function hasPendingApplication(): bool
    {
        return $this->applications()->where('status', 'pending')->exists();
    }

    public function canReceiveApplication(): bool
    {
        return $this->status !== 'inactive'
            && !$this->needs_verification
            && $this->building()->where('is_active', true)->exists()
            && $this->remainingCapacity() > 0;
    }

    public function closeExpiredLeases(): void
    {
        $expired = $this->leases()->where('status', 'active')->whereDate('end_date', '<', today())->get();
        if ($expired->isEmpty()) return;

        foreach ($expired as $lease) {
            $lease->update(['status' => 'expired']);
            DB::table('stall_occupants')->where('lease_id', $lease->id)->update([
                'end_date' => $lease->end_date?->toDateString(),
                'updated_at' => now(),
            ]);
            DB::table('activity_logs')->insert([
                'user_id' => auth()->id(),
                'action' => 'lease.expired',
                'subject_type' => Lease::class,
                'subject_id' => $lease->id,
                'details' => json_encode(['stall_id' => $this->id, 'registrant_id' => $lease->registrant_id]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $currentLease = $this->leases()->where('status', 'active')
            ->where(fn ($query) => $query->whereNull('end_date')->orWhereDate('end_date', '>=', today()))
            ->orderBy('id')->first();
        $this->forceFill([
            'registrant_id' => $currentLease?->registrant_id,
            'status' => $currentLease || ($this->source_occupant_name && $this->shared_capacity > 1)
                ? 'occupied'
                : 'available',
        ])->save();
    }

    public function addSharedOccupant(int $registrantId, int $approvedBy): Lease
    {
        return DB::transaction(function () use ($registrantId, $approvedBy) {
            $stall = self::query()->lockForUpdate()->findOrFail($this->id);
            if ($stall->shared_capacity <= 1 || $stall->occupiedCapacity() >= $stall->shared_capacity) {
                throw new \DomainException('This shared stall has no remaining occupant capacity.');
            }

            $lease = Lease::create([
                'stall_id' => $stall->id,
                'registrant_id' => $registrantId,
                'start_date' => today()->toDateString(),
                'end_date' => today()->addYear()->toDateString(),
                'monthly_rate' => $stall->monthly_rate,
                'rental_amount' => $stall->rental_rate ?? $stall->monthly_rate,
                'payment_frequency' => $stall->payment_frequency ?: 'monthly',
                'approved_by' => $approvedBy,
                'status' => 'active',
            ]);
            $stall->occupants()->attach($registrantId, [
                'lease_id' => $lease->id,
                'start_date' => today()->toDateString(),
            ]);
            $stall->forceFill([
                'registrant_id' => $stall->registrant_id ?: $registrantId,
                'status' => 'occupied',
            ])->save();

            return $lease;
        }, 5);
    }

    /**
     * Single place where ownership changes. Ends the old lease, opens a new one
     * and keeps stall status/owner in sync automatically.
     */
    public function assignOwner(?int $registrantId, ?int $approvedBy = null, bool $notifyAssignee = false): void
    {
        DB::transaction(function () use ($registrantId, $approvedBy, $notifyAssignee) {
            $stall = self::query()->lockForUpdate()->findOrFail($this->id);
            if ($stall->registrant_id === $registrantId) {
                return;
            }

            $previousRegistrant = $stall->registrant_id;
            $activeLeases = $stall->leases()->where('status', 'active')->get();
            $activeLeases->each(function (Lease $lease) {
                $lease->update(['status' => 'ended', 'end_date' => today()->toDateString()]);
                DB::table('stall_occupants')->where('lease_id', $lease->id)->update([
                    'end_date' => today()->toDateString(),
                    'updated_at' => now(),
                ]);
            });
            if ($registrantId) {
                $lease = Lease::create([
                    'stall_id' => $stall->id,
                    'registrant_id' => $registrantId,
                    'start_date' => today()->toDateString(),
                    'end_date' => today()->addYear()->toDateString(),
                    'monthly_rate' => $stall->monthly_rate,
                    'rental_amount' => $stall->rental_rate ?? $stall->monthly_rate,
                    'payment_frequency' => $stall->payment_frequency ?: 'monthly',
                    'approved_by' => $approvedBy,
                    'status' => 'active',
                ]);
                $stall->occupants()->attach($registrantId, [
                    'lease_id' => $lease->id,
                    'start_date' => today()->toDateString(),
                ]);
                $stall->forceFill(['registrant_id' => $registrantId, 'status' => 'occupied'])->save();
            } else {
                $stall->forceFill(['registrant_id' => null, 'status' => 'available'])->save();
            }

            DB::table('activity_logs')->insert([
                'user_id' => auth()->id(),
                'action' => $registrantId ? 'stall.occupant_changed' : 'stall.occupant_removed',
                'subject_type' => self::class,
                'subject_id' => $stall->id,
                'details' => json_encode([
                    'previous_registrant_id' => $previousRegistrant,
                    'previous_source_occupant' => $stall->source_occupant_name,
                    'new_registrant_id' => $registrantId,
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            if ($registrantId && $stall->shared_capacity <= 1 && $stall->source_occupant_name) {
                $stall->forceFill(['source_occupant_name' => null])->save();
            }
            if ($registrantId && $notifyAssignee) {
                $registrant = Registrant::with('user')->find($registrantId);
                $registrant?->user?->notify(new \App\Notifications\MarketSystemNotification(
                    'Stall assignment updated',
                    'You have been assigned stall ' . $stall->stall_number . '.',
                    route('my.leases'),
                ));
            }
        });

        $this->refresh();
    }
}
