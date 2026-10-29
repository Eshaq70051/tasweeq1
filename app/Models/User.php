<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Services\FcmService;

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
        'fcm_token',
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

    public function deviceTokens()
    {
        return $this->hasMany(DeviceToken::class);
    }

    public function userNotifications()
    {
        return $this->hasMany(UserNotification::class)->orderBy('created_at', 'desc');
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

    /**
     * إرسال إشعار فوري وتخزينه في قاعدة البيانات
     */
    public function sendPushNotification(string $title, string $body, array $data = [], string $type = 'general'): ?UserNotification
    {
        return app(FcmService::class)->sendToUser($this, $title, $body, $data, $type);
    }
}
