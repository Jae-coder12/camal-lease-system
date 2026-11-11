<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    protected $fillable = ['registrant_id', 'stall_id', 'purpose', 'status', 'remarks', 'reviewed_at'];
    protected $casts = ['reviewed_at' => 'datetime'];

    public function registrant() { return $this->belongsTo(Registrant::class); }
    public function stall() { return $this->belongsTo(Stall::class); }
}
