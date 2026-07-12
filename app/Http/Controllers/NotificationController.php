<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Liste des notifications de l'utilisateur connecté (les 30 plus récentes)
     */
    public function index()
    {
        $user = Auth::user();

        $notifications = $user->notifications()->latest()->take(30)->get();

        return response()->json([
            'notifications'  => $notifications,
            'unread_count'   => $user->unreadNotifications()->count(),
        ]);
    }

    /**
     * Marquer une notification précise comme lue
     */
    public function markAsRead($id)
    {
        $notification = Auth::user()->notifications()->where('id', $id)->first();

        if (! $notification) {
            return response()->json(['message' => 'Notification introuvable'], 404);
        }

        if (! $notification->read_at) {
            $notification->markAsRead();
        }

        return response()->json(['message' => 'Notification marquée comme lue']);
    }

    /**
     * Marquer toutes les notifications comme lues
     */
    public function markAllAsRead()
    {
        Auth::user()->unreadNotifications->markAsRead();

        return response()->json(['message' => 'Toutes les notifications ont été marquées comme lues']);
    }
}
