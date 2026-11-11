<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lease extends Model
{
    protected $fillable = ['contract_number', 'stall_id', 'registrant_id', 'start_date', 'end_date', 'monthly_rate', 'status'];
    protected $casts = ['start_date' => 'date', 'end_date' => 'date'];

    protected static function booted(): void
    {
        static::created(function (Lease $l) {
            $l->updateQuietly(['contract_number' => sprintf('LC-%d-%04d', now()->year, $l->id)]);
        });
    }

    public function stall() { return $this->belongsTo(Stall::class); }
    public function registrant() { return $this->belongsTo(Registrant::class); }
}
