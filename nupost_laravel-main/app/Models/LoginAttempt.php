<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoginAttempt extends Model
{
    protected $table = 'login_attempts';

    public $timestamps = false;

    protected $fillable = [
        'email',
        'ip_address',
        'success',
        'attempted_at',
    ];

    protected $casts = [
        'success'      => 'boolean',
        'attempted_at' => 'datetime',
    ];

    public static function isRateLimited(string $email): bool
    {
        return static::where('email', $email)
            ->where('success', false)
            ->where('attempted_at', '>', now()->subMinutes(15))
            ->count() >= 5;
    }

    public static function countRecent(string $email): int
    {
        return static::where('email', $email)
            ->where('success', false)
            ->where('attempted_at', '>', now()->subMinutes(15))
            ->count();
    }
}