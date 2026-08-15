<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\System\Notification;

class NotificationController extends Controller
{
    public function getMyNotifications(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['error' => 'Non authentifié'], 401);
        }

        $notifications = Notification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->take(50)
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $notifications
        ]);
    }

    public function markAsRead(Request $request, $id)
    {
        $notification = Notification::where('user_id', $request->user()?->id)->findOrFail($id);

        $notification->update(['read_at' => now()]);

        return response()->json([
            'status' => 'success',
            'message' => 'Notification marquée comme lue.'
        ]);
    }

    public function markAllAsRead(Request $request)
    {
        Notification::where('user_id', $request->user()?->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json([
            'status' => 'success',
            'message' => 'Toutes les notifications ont été marquées comme lues.'
        ]);
    }
}
