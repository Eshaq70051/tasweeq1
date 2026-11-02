<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OtpVerification extends Model
{
    protected $fillable = [
        'phone',
        'code',
        'type',
        'name',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function isValid(string $inputCode): bool
    {
        return $this->code === $inputCode && now()->lt($this->expires_at);
    }
}
