<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostRequest extends Model
{
    protected $table = 'post_requests';

    protected $fillable = [
        'request_id',
        'user_id',
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

    // SLA Lead Time in days by Category
    public function getSlaDaysAttribute(): int
    {
        return match((string) ($this->category ?? 'General')) {
            'Event Documentation' => 30,
            'New Campaign / Creative Concept' => 10,
            'Multiple Collaterals / Tarpaulins' => 5,
            'Standard PubMat' => 3,
            'Template-Based PubMat' => 2,
            default => 1,
        };
    }

    // SLA Slack Days (positive = days remaining before production must start, negative = overdue)
    public function getSlackDaysAttribute(): int
    {
        $prefDate = $this->preferred_date ? \Carbon\Carbon::parse($this->preferred_date)->startOfDay() : now()->addDays(3)->startOfDay();
        $latestStart = $prefDate->copy()->subDays($this->sla_days);
        $today = now()->startOfDay();
        return (int) $today->diffInDays($latestStart, false);
    }

    // At risk if slack is negative
    public function getIsAtRiskAttribute(): bool
    {
        return $this->slack_days < 0;
    }

    // Due soon if slack is 0 or 1 day
    public function getIsDueSoonAttribute(): bool
    {
        return $this->slack_days >= 0 && $this->slack_days <= 1;
    }

    // Priority weight for sorting: Urgent=0, High=1, Medium=2, Low=3
    public function getPrioWeightAttribute(): int
    {
        return match(strtolower(trim((string) ($this->priority ?? 'Low')))) {
            'urgent' => 0,
            'high' => 1,
            'medium' => 2,
            default => 3,
        };
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