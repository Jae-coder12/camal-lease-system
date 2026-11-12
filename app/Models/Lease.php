<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Lease extends Model
{
    protected $fillable = [
        'contract_number', 'stall_id', 'registrant_id', 'start_date', 'end_date',
        'monthly_rate', 'rental_amount', 'payment_frequency', 'approved_by', 'status',
        'application_id', 'contract_terms', 'tenant_details', 'renewed_from_lease_id', 'terminated_at', 'terminated_by',
    ];
    protected $casts = [
        'start_date' => 'date', 'end_date' => 'date', 'rental_amount' => 'decimal:2',
        'tenant_details' => 'array', 'terminated_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Lease $lease) {
            if (!$lease->tenant_details) {
                $registrant = Registrant::find($lease->registrant_id);
                $lease->tenant_details = $registrant ? [
                    'name' => $registrant->name,
                    'control_number' => $registrant->control_number,
                    'civil_status' => $registrant->status,
                    'citizenship' => $registrant->citizenship,
                    'birthdate' => $registrant->birthdate?->toDateString(),
                    'address' => $registrant->address,
                    'contact_number' => $registrant->contact_number,
                    'email' => $registrant->user?->email,
                ] : null;
            }

            $lease->contract_terms ??= self::defaultContractTerms();
        });

        static::created(function (Lease $l) {
            $l->updateQuietly(['contract_number' => sprintf('LC-%d-%04d', now()->year, $l->id)]);
        });
    }

    public function stall(): BelongsTo { return $this->belongsTo(Stall::class); }
    public function registrant(): BelongsTo { return $this->belongsTo(Registrant::class); }
    public function application(): BelongsTo { return $this->belongsTo(Application::class); }
    public function approver(): BelongsTo { return $this->belongsTo(User::class, 'approved_by'); }
    public function terminator(): BelongsTo { return $this->belongsTo(User::class, 'terminated_by'); }
    public function renewedFrom(): BelongsTo { return $this->belongsTo(self::class, 'renewed_from_lease_id'); }
    public function renewal(): HasOne { return $this->hasOne(self::class, 'renewed_from_lease_id'); }
    public function payments() { return $this->hasMany(Payment::class); }

    public static function defaultContractTerms(): string
    {
        return implode("\n", [
            'The lessee shall personally conduct the approved business in the stall. The lessee may engage helpers but remains responsible for the business and compliance with the Market Code and applicable municipal ordinances, rules, and regulations.',
            'The stall shall be used for its approved market business and not for residential purposes, except where residential use is allowed by the Market Code for a qualifying second-floor market unit.',
            'The lessee shall not sell or transfer the privilege to occupy the stall or sublease it to another person.',
            'Goods and merchandise left in the stall or public market after closing remain at the lessee’s risk. The Municipality is not responsible for loss or damage caused by fire, theft, robbery, force majeure, or other circumstances beyond its control.',
        ]);
    }

    public function getMonitoringStatusAttribute(): string
    {
        if (in_array($this->status, ['ended', 'terminated'], true)) return 'Terminated';
        if ($this->status === 'expired') return 'Expired';
        if ($this->status !== 'active') return ucfirst($this->status);
        if ($this->start_date && $this->start_date->gt(today())) return 'Upcoming';
        if ($this->end_date && $this->end_date->lt(today())) return 'Expired';

        $warningDays = (int) config('market.lease_warning_days', 30);
        return $this->end_date && today()->diffInDays($this->end_date, false) <= $warningDays
            ? 'Expiring Soon'
            : 'Active';
    }

    public function getOutstandingBalanceAttribute(): ?float
    {
        if ($this->payment_frequency !== 'monthly') return null;

        $through = $this->end_date && $this->end_date->lt(today()) ? $this->end_date : today();
        if ($this->start_date->gt($through)) return 0.0;

        $monthsDue = $this->start_date->copy()->startOfMonth()
            ->diffInMonths($through->copy()->startOfMonth()) + 1;
        $charges = $monthsDue * (float) ($this->rental_amount ?? $this->monthly_rate);

        return max(0, round($charges - (float) $this->payments()->where('status', 'paid')->sum('amount'), 2));
    }
}
