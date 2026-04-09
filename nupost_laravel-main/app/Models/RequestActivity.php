<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequestActivity extends Model
{
    protected $table = 'request_activity';

    protected $fillable = [
        'request_id',
        'actor',
        'action',
    ];

    public function postRequest()
    {
        return $this->belongsTo(PostRequest::class, 'request_id');
    }
}