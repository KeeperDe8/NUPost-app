<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostRequest extends Model
{
    protected $table = 'post_requests';

    protected $fillable = [
        'request_id',
        'title',
        'requester',
        'category',
        'priority',
        'status',
        'description',
        'platform',
        'caption',
        'preferred_date',
        'media_file',
    ];

    protected $casts = [
        'preferred_date' => 'date',
    ];

    // Status constants
    const STATUS_PENDING  = 'Pending Review';
    const STATUS_REVIEW   = 'Under Review';
    const STATUS_APPROVED = 'Approved';
    const STATUS_POSTED   = 'Posted';
    const STATUS_REJECTED = 'Rejected';

    public function comments()
    {
        return $this->hasMany(RequestComment::class, 'request_id');
    }

    public function activities()
    {
        return $this->hasMany(RequestActivity::class, 'request_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'requester', 'name');
    }

    // Helper: get first media file
    public function getFirstMediaAttribute(): string
    {
        $files = array_filter(array_map('trim', explode(',', $this->media_file ?? '')));
        return $files ? reset($files) : '';
    }

    // Helper: get all media files as array
    public function getMediaFilesAttribute(): array
    {
        return array_filter(array_map('trim', explode(',', $this->media_file ?? '')));
    }

    // Helper: get platforms as array
    public function getPlatformsArrayAttribute(): array
    {
        return array_filter(array_map('trim', explode(',', $this->platform ?? '')));
    }

    // Boot: auto-generate request_id
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->request_id)) {
                $last = static::max('id') ?? 0;
                $model->request_id = 'REQ-' . str_pad($last + 1, 5, '0', STR_PAD_LEFT);
            }
        });
    }
}