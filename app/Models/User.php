<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;
    use HasFactory;

    protected $fillable = ['name', 'email', 'username', 'password', 'role'];
    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return ['email_verified_at' => 'datetime', 'password' => 'hashed'];
    }

    public function registrant() { return $this->hasOne(Registrant::class); }
    public function isAdmin(): bool { return $this->role === 'admin'; }
    public function isApplicant(): bool { return $this->role === 'user'; }
}
