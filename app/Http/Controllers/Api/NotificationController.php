<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function __construct(private NotificationService $notificationService) {}

    public function index(Request $request)
    {
        $unreadOnly = $request->boolean('unread_only');
        $notifications = $this->notificationService->getNotifications($unreadOnly);

        return response()->json([
            'status' => 'success',
            'data' => [
                'items' => $notifications->items(),
                'pagination' => [
                    'current_page' => $notifications->currentPage(),
                    'last_page' => $notifications->lastPage(),
                    'total' => $notifications->total(),
                    'per_page' => $notifications->perPage(),
                ],
            ],
        ], 200);
    }

    public function unreadCount()
    {
        $count = $this->notificationService->getUnreadCount();

        return response()->json([
            'status' => 'success',
            'data' => ['unread_count' => $count],
        ], 200);
    }

    public function show($id)
    {
        $notification = $this->notificationService->getNotifications()
            ->where('id', $id)
            ->firstOrFail();

        return response()->json([
            'status' => 'success',
            'data' => $notification,
        ], 200);
    }

    public function markAsRead($id)
    {
        $this->notificationService->markAsRead((int) $id);

        return response()->json([
            'status' => 'success',
            'message' => 'Đánh dấu đã đọc thành công',
        ], 200);
    }

    public function markAllAsRead()
    {
        $count = $this->notificationService->markAllAsRead();

        return response()->json([
            'status' => 'success',
            'message' => "Đã đánh dấu {$count} thông báo là đã đọc",
        ], 200);
    }
}