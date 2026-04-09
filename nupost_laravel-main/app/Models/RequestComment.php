<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequestComment extends Model
{
    protected $table = 'request_comments';

    protected $fillable = [
        'request_id',
        'sender_role',
        'sender_name',
        'message',
    ];

    public function postRequest()
    {
        return $this->belongsTo(PostRequest::class, 'request_id');
    }
}