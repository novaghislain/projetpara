<?php
namespace App\Traits;

use App\Models\Notification;
use App\Models\User;

trait Notifiable
{
    public static function send($userId, $type, $title, $message, $data = null): Notification
    {
        return Notification::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => $data ? json_encode($data) : null,
        ]);
    }

    public static function sendToClient($clientId, $type, $title, $message, $data = null): void
    {
        $users = User::where('client_id', $clientId)->get();
        foreach ($users as $user) {
            self::send($user->id, $type, $title, $message, $data);
        }
    }

    public static function sendToAllAdmins($type, $title, $message, $data = null): void
    {
        $admins = User::where('is_company_admin', true)->orWhere('role', 'super_admin')->get();
        foreach ($admins as $user) {
            self::send($user->id, $type, $title, $message, $data);
        }
    }
}
