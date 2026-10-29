<?php

namespace App\Models;
//المستخدمين
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_admin',
        'google_id',
        'is_google_account',
        'status',
        'is_available',
        'phone',
        'avatar',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_admin' => 'boolean',
        'is_google_account' => 'boolean',
        'is_available' => 'boolean',
    ];

    public function cart()
    {
        return $this->hasMany(Cart::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function isAdmin()
    {
        return $this->is_admin === true || $this->role === 'admin';
    }

    public function isCaptain(): bool
    {
        return strtolower((string) $this->role) === 'captain';
    }

    public function captainOrders()
    {
        return $this->hasMany(Order::class, 'assigned_captain_id');
    }
}
