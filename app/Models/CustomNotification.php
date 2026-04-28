<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomNotification extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'url',
        'is_read',
        'read_at'
    ];

    // Relation para malaman kung kanino itong notif
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}