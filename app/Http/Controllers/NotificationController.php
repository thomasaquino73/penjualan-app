<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Show all notifications (both unread & read)
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // Dapatkan prefix, contoh: 'bjid'
        $prefix = $request->segment(1);

        // Query unread dengan filter JSON langsung
        $unreadNotifications = $user->unreadNotifications()
            ->where('data->module_app', $prefix)
            ->paginate(10, ['*'], 'unread_page');

        // Query read dengan filter JSON langsung
        $readNotifications = $user->readNotifications()
            ->where('data->module_app', $prefix)
            ->paginate(10, ['*'], 'read_page');

        return view('notifications.notifications_index', compact(
            'unreadNotifications',
            'readNotifications'
        ));
    }

    /**
     * Mark a notification as read (AJAX)
     */
    public function markAsRead(Request $request)
    {
        $notificationId = $request->input('id');
        $notification = auth()->user()->unreadNotifications()->find($notificationId);

        if ($notification) {
            $notification->markAsRead();

            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false], 404);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        auth()->user()->unreadNotifications->markAsRead();

        return response()->json(['success' => true]);
    }

    /**
     * Delete all read notifications
     */
    public function deleteRead()
    {
        auth()->user()->readNotifications()->delete();

        return response()->json(['success' => true]);
    }
}
