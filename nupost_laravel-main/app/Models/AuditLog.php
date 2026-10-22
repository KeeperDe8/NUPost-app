<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $table = 'audit_logs';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'action_type',
        'ip_address',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Create an audit log entry.
     *
     * @param string $actionType
     * @param int|null $userId
     * @return self
     */
    public static function record(string $actionType, ?int $userId = null): self
    {
        if ($userId === null) {
            if (session()->has('user_id')) {
                $userId = (int) session('user_id');
            } elseif (session()->has('admin_email')) {
                $admin = User::where('email', session('admin_email'))->first();
                if ($admin) {
                    $userId = $admin->id;
                }
            }
        }

        return self::create([
            'user_id' => $userId,
            'action_type' => $actionType,
            'ip_address' => request()->ip(),
            'created_at' => now(),
        ]);
    }
}
