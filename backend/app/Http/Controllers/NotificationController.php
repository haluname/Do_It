<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notification;

class NotificationController extends Controller
{

    public function index(Request $request)
    {
        $query = Notification::where('user_id', $request->user()->id)
            ->with('notifiable')
            ->orderBy('created_at', 'desc');

        if ($request->has('unread') && $request->unread) {
            $query->whereNull('read_at');
        }

        $notifications = $query->paginate(10);

        return response()->json([
            'data' => $notifications->items(),
            'meta' => [
                'total' => $notifications->total(),
                'unread_count' => $request->user()->unreadNotifications()->count()
            ]
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'type' => 'required|string',
            'notifiable_id' => 'required|integer',
            'notifiable_type' => 'required|string',
            'message' => 'required|string'
        ]);

        $notification = Notification::create($validated);

        return response()->json($notification, 201);
    }

    public function markAsRead($id)
    {
        $notification = Notification::findOrFail($id);

        if (!$notification->read_at) {
            $notification->markAsRead();
        }

        return response()->json($notification);
    }

    public function markAllAsRead(Request $request)
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return response()->json(['message' => 'Tutte le notifiche segnate come lette']);
    }
}
