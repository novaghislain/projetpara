<?php

namespace App\Channels;

use Illuminate\Notifications\Notification;

class CustomDatabaseChannel
{
    /**
     * Send the given notification.
     */
    public function send(object $notifiable, Notification $notification): void
    {
        $data = $notification->toArray($notifiable);

        $notifiable->notifications()->create([
            'title' => $data['title'] ?? 'Notification',
            'message' => $data['description'] ?? '',
            'type' => $data['type'] ?? 'info',
            'data' => $data,
        ]);
    }
}
