<?php

namespace App\Notifications;

use App\Events\NotificationRecuEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class RealTimeNotification extends Notification
{
    use Queueable;

    protected $title;
    protected $description;
    protected $link;
    protected $icon;

    /**
     * Create a new notification instance.
     */
    public function __construct($title, $description, $link, $icon = 'fas fa-info-circle')
    {
        $this->title = $title;
        $this->description = $description;
        $this->link = $link;
        $this->icon = $icon;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return [\App\Channels\CustomDatabaseChannel::class];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $data = [
            'title' => $this->title,
            'description' => $this->description,
            'link' => $this->link,
            'icon' => $this->icon,
            'time' => 'À l\'instant',
        ];

        // Déclencher l'événement Websocket Reverb
        try {
            event(new NotificationRecuEvent($notifiable->id, $data));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Impossible de diffuser la notification en temps réel: ' . $e->getMessage());
        }

        return $data;
    }
}
