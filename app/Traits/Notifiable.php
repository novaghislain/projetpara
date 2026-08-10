<?php

namespace App\Traits;

use App\Models\Notification;
use App\Models\User;

trait Notifiable
{
    /**
     * Crée une notification pour un utilisateur spécifique.
     *
     * @param int $userId
     * @param string $type (info, success, warning, error)
     * @param string $title
     * @param string $message
     * @param array|null $data
     * @return \App\Models\Notification
     */
    public static function send($userId, $type, $title, $message, $data = null)
    {
        return Notification::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => $data,
        ]);
    }

    /**
     * Crée une notification pour tous les utilisateurs d'un client (entreprise).
     *
     * @param int $clientId
     * @param string $type (info, success, warning, error)
     * @param string $title
     * @param string $message
     * @param array|null $data
     * @return array
     */
    public static function sendToClient($clientId, $type, $title, $message, $data = null)
    {
        $users = User::where('client_id', $clientId)->get();
        $notifications = [];

        foreach ($users as $user) {
            $notifications[] = self::send($user->id, $type, $title, $message, $data);
        }

        return $notifications;
    }
}
