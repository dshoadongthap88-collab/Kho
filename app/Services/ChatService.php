<?php

namespace App\Services;

use App\Models\ChatMessage;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;

class ChatService
{
    public function getConversation(int $userId, ?int $partnerId = null, ?string $fromDate = null, ?string $toDate = null)
    {
        $query = ChatMessage::query()
            ->with(['sender', 'recipient'])
            ->where(function ($q) use ($userId) {
                $q->where('user_id', $userId)
                  ->orWhere('recipient_id', $userId);
            });

        if ($partnerId) {
            $query->where(function ($q) use ($userId, $partnerId) {
                $q->where(function ($q2) use ($userId, $partnerId) {
                    $q2->where('user_id', $userId)->where('recipient_id', $partnerId);
                })->orWhere(function ($q2) use ($userId, $partnerId) {
                    $q2->where('user_id', $partnerId)->where('recipient_id', $userId);
                });
            });
        }

        if ($fromDate) {
            $query->whereDate('created_at', '>=', $fromDate);
        }
        if ($toDate) {
            $query->whereDate('created_at', '<=', $toDate);
        }

        return $query->orderBy('created_at', 'desc')->paginate(30);
    }

    public function sendMessage(array $data): ChatMessage
    {
        $data['user_id'] = Auth::id();

        if (empty($data['recipient_id'])) {
            $data['recipient_id'] = null;
        }

        $message = ChatMessage::create($data);

        if ($message->recipient_id) {
            Notification::create([
                'user_id' => $message->recipient_id,
                'type' => 'chat',
                'title' => 'Tin nhắn mới',
                'message' => $message->sender->name . ': ' . ($message->content ?? 'Đã gửi một tệp đính kèm'),
                'data' => [
                    'chat_message_id' => $message->id,
                    'sender_id' => $message->user_id,
                    'sender_name' => $message->sender->name,
                ],
            ]);
        }

        $message->load('sender', 'recipient');
        return $message;
    }

    public function markAsRead(int $messageId): bool
    {
        $message = ChatMessage::where('recipient_id', Auth::id())
            ->where('id', $messageId)
            ->firstOrFail();

        $message->update(['is_read' => true]);
        return true;
    }

    public function getUnreadCount(int $userId): int
    {
        return ChatMessage::where('recipient_id', $userId)
            ->where('is_read', false)
            ->count();
    }
}