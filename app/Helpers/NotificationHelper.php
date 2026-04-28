<?php

namespace App\Helpers;

use App\Models\CustomNotification;

class NotificationHelper
{
    /**
     * Send a custom notification to a specific user.
     */
    public static function send($userId, $title, $message, $url = null, $type = 'info')
    {
        return CustomNotification::create([
            'user_id' => $userId,
            'title'   => $title,
            'message' => $message,
            'url'     => $url,
            'type'    => $type,
            'is_read' => false,
        ]);
    }
}