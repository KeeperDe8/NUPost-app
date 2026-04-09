<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'is_verified',
        'phone',
        'organization',
        'department',
        'bio',
        'profile_photo',
        'email_notif',
        'status_updates',
        'public_profile',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'is_verified'    => 'boolean',
        'email_notif'    => 'boolean',
        'status_updates' => 'boolean',
        'public_profile' => 'boolean',
    ];

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function postRequests()
    {
        return $this->hasMany(PostRequest::class, 'requester', 'name');
    }

    public function rememberedDevices()
    {
        return $this->hasMany(RememberedDevice::class);
    }
}