<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OtpAttempt extends Model
{
    protected $table = 'otp_attempts';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'success',
        'attempted_at',
    ];

    protected $casts = [
        'success'      => 'boolean',
        'attempted_at' => 'datetime',
    ];
}