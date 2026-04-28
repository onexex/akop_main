<?php

// app/Http/Controllers/NotificationController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    // public function markAsRead($id)
    // {
    //     $notif = CustomNotification::where('id', $id)
    //                 ->where('user_id', auth()->id()) // Siguraduhin na sa kanya ito
    //                 ->firstOrFail();

    //     $notif->update(['is_read' => true,'read_at' => now()]);

    //     return response()->json(['message' => 'Read success']);
    // }

    public function markAsRead($id)
    {
        $notif = auth()->user()->customNotifications()->findOrFail($id);
        $notif->update(['is_read' => true]);

        // 1. Check kung ang request ay galing sa AXIOS (Bell Dropdown)
        if (request()->expectsJson()) {
            return response()->json(['message' => 'Read success']);
        }

        // 2. Kung galing sa INERTIA (Index Page Button/router.post)
        // Ito ang gamot sa error mo. Kailangan ni Inertia ang 'back()'
        // para i-refresh ang props nang hindi nag-e-error.
        return back();
    }

    public function index()
    {
        return inertia('Notifications/Index', [
            // Sa iyong NotificationController.php
            'notifications' => auth()->user()->customNotifications()
                ->latest()
                ->paginate(15)
                ->through(fn($n) => [
                    'id'         => $n->id,
                    'title'      => $n->title,
                    'message'    => $n->message,
                    'url'        => $n->url,
                    'is_read'    => $n->is_read,
                    'created_at' => $n->created_at->diffForHumans(), // Lalabas na "2 minutes ago"
                ]),
        ]);
    }
    public function markAllAsRead(Request $request)
    {
        auth()->user()->customNotifications()
            ->where('is_read', false)
            ->update(['is_read' => true]);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'All marked as read']);
        }

        return back();
    }
}
