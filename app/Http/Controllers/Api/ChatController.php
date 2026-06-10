<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ChatService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    public function __construct(private ChatService $chatService) {}

    public function index(Request $request)
    {
        $partnerId = $request->query('partner_id') ? (int) $request->query('partner_id') : null;
        $fromDate = $request->query('from_date');
        $toDate = $request->query('to_date');

        $messages = $this->chatService->getConversation(Auth::id(), $partnerId, $fromDate, $toDate);

        return response()->json([
            'status' => 'success',
            'data' => [
                'items' => $messages->items(),
                'pagination' => [
                    'current_page' => $messages->currentPage(),
                    'last_page' => $messages->lastPage(),
                    'total' => $messages->total(),
                    'per_page' => $messages->perPage(),
                ],
            ],
        ], 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'recipient_id' => 'nullable|exists:users,id',
            'type' => 'required|in:text,file,image',
            'content' => 'nullable|string|max:5000',
            'attachment_path' => 'nullable|string|max:500',
        ]);

        $message = $this->chatService->sendMessage($request->only(['recipient_id', 'type', 'content', 'attachment_path']));

        return response()->json([
            'status' => 'success',
            'message' => 'Gửi tin nhắn thành công',
            'data' => $message,
        ], 201);
    }

    public function markAsRead($id)
    {
        $this->chatService->markAsRead((int) $id);

        return response()->json([
            'status' => 'success',
            'message' => 'Đánh dấu đã đọc thành công',
        ], 200);
    }

    public function unreadCount()
    {
        $count = $this->chatService->getUnreadCount(Auth::id());

        return response()->json([
            'status' => 'success',
            'data' => ['unread_count' => $count],
        ], 200);
    }
}