<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KayeToken extends Model
{
    protected $table = 'kaye_tokens';

    protected $fillable = [
        'token',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    /**
     * Check if the token is currently valid.
     *
     * @return bool
     */
    public function isValid(): bool
    {
        return $this->expires_at->isFuture();
    }
}
