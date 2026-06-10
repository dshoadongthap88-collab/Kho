<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class NotificationService
{
    public function getNotifications(?bool $unreadOnly = false, int $perPage = 20)
    {
        $query = Notification::where('user_id', Auth::id())
            ->with('user')
            ->orderBy('created_at', 'desc');

        if ($unreadOnly) {
            $query->where('is_read', false);
        }

        return $query->paginate($perPage);
    }

    public function getUnreadCount(): int
    {
        return Notification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->count();
    }

    public function markAsRead(int $notificationId): bool
    {
        $notification = Notification::where('user_id', Auth::id())
            ->where('id', $notificationId)
            ->firstOrFail();

        $notification->update([
            'is_read' => true,
            'read_at' => now(),
        ]);

        return true;
    }

    public function markAllAsRead(): int
    {
        return Notification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
    }

    public function createNotification(int $userId, string $type, string $title, string $message, ?array $data = null): Notification
    {
        return Notification::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => $data,
        ]);
    }
}