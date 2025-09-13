<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = [
        'from_user_id',
        'subject',
        'body',
        'target',
        'read_at',       // ← penting
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];
}
