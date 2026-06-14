<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\ChatMessage;
use App\Models\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatNotificationApiTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private User $user2;
    private string $token;
    private string $token2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'code' => 'USR001',
            'name' => 'Nguyen Van A',
            'email' => 'nguyenvana@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'permissions' => ['*'],
            'status' => 'active',
        ]);
        $this->user2 = User::create([
            'code' => 'USR002',
            'name' => 'Tran Thi B',
            'email' => 'tranthib@example.com',
            'password' => bcrypt('password'),
            'role' => 'sales',
            'permissions' => ['*'],
            'status' => 'active',
        ]);

        $this->token = $this->user->createToken('test')->plainTextToken;
        $this->token2 = $this->user2->createToken('test2')->plainTextToken;
    }

    private function withAuth(array $headers = []): array
    {
        return array_merge($headers, ['Authorization' => 'Bearer ' . $this->token]);
    }

    private function withAuth2(array $headers = []): array
    {
        return array_merge($headers, ['Authorization' => 'Bearer ' . $this->token2]);
    }

    // ==================== CHAT ====================

    public function test_can_send_chat_message(): void
    {
        $response = $this->postJson('/api/chat', [
            'recipient_id' => $this->user2->id,
            'type' => 'text',
            'content' => 'Xin chao ban',
        ], $this->withAuth());

        $response->assertStatus(201)
            ->assertJson(['status' => 'success', 'message' => 'Gửi tin nhắn thành công']);

        $this->assertDatabaseHas('chat_messages', [
            'user_id' => $this->user->id,
            'recipient_id' => $this->user2->id,
            'type' => 'text',
            'content' => 'Xin chao ban',
        ]);
    }

    public function test_can_list_chat_messages(): void
    {
        ChatMessage::create([
            'user_id' => $this->user->id,
            'recipient_id' => $this->user2->id,
            'type' => 'text',
            'content' => 'Tin nhan 1',
        ]);
        ChatMessage::create([
            'user_id' => $this->user2->id,
            'recipient_id' => $this->user->id,
            'type' => 'text',
            'content' => 'Tin nhan 2',
        ]);

        $response = $this->getJson('/api/chat?partner_id=' . $this->user2->id, $this->withAuth());

        $response->assertStatus(200)
            ->assertJson(['status' => 'success'])
            ->assertJsonCount(2, 'data.items');
    }

    public function test_can_filter_chat_by_partner(): void
    {
        ChatMessage::create([
            'user_id' => $this->user->id,
            'recipient_id' => $this->user2->id,
            'type' => 'text',
            'content' => 'To user2',
        ]);
        ChatMessage::create([
            'user_id' => $this->user->id,
            'recipient_id' => null,
            'type' => 'text',
            'content' => 'Broadcast',
        ]);

        $response = $this->getJson('/api/chat?partner_id=' . $this->user2->id, $this->withAuth());

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data.items')
            ->assertJsonPath('data.items.0.content', 'To user2');
    }

    public function test_can_mark_chat_as_read(): void
    {
        $message = ChatMessage::create([
            'user_id' => $this->user->id,
            'recipient_id' => $this->user2->id,
            'type' => 'text',
            'content' => 'Tin nhan chua doc',
            'is_read' => false,
        ]);

        $response = $this->postJson("/api/chat/{$message->id}/read", [], $this->withAuth2());

        $response->assertStatus(200)
            ->assertJson(['status' => 'success', 'message' => 'Đánh dấu đã đọc thành công']);

        $this->assertDatabaseHas('chat_messages', ['id' => $message->id, 'is_read' => true]);
    }

    public function test_can_get_unread_chat_count(): void
    {
        ChatMessage::create([
            'user_id' => $this->user->id,
            'recipient_id' => $this->user2->id,
            'type' => 'text',
            'content' => 'Tin 1',
            'is_read' => false,
        ]);
        ChatMessage::create([
            'user_id' => $this->user->id,
            'recipient_id' => $this->user2->id,
            'type' => 'text',
            'content' => 'Tin 2',
            'is_read' => true,
        ]);

        $response = $this->getJson('/api/chat/unread-count', $this->withAuth2());

        $response->assertStatus(200)
            ->assertJson(['status' => 'success'])
            ->assertJsonPath('data.unread_count', 1);
    }

    // ==================== NOTIFICATIONS ====================

    public function test_can_list_notifications(): void
    {
        Notification::create([
            'user_id' => $this->user->id,
            'type' => 'system',
            'title' => 'Thong bao 1',
            'message' => 'Noi dung 1',
        ]);
        Notification::create([
            'user_id' => $this->user->id,
            'type' => 'chat',
            'title' => 'Thong bao 2',
            'message' => 'Noi dung 2',
        ]);

        $response = $this->getJson('/api/notifications', $this->withAuth());

        $response->assertStatus(200)
            ->assertJson(['status' => 'success'])
            ->assertJsonCount(2, 'data.items');
    }

    public function test_can_filter_notifications_unread_only(): void
    {
        Notification::create([
            'user_id' => $this->user->id,
            'type' => 'system',
            'title' => 'Chua doc',
            'message' => 'Noi dung',
            'is_read' => false,
        ]);
        Notification::create([
            'user_id' => $this->user->id,
            'type' => 'chat',
            'title' => 'Da doc',
            'message' => 'Noi dung',
            'is_read' => true,
        ]);

        $response = $this->getJson('/api/notifications?unread_only=1', $this->withAuth());

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data.items')
            ->assertJsonPath('data.items.0.title', 'Chua doc');
    }

    public function test_can_mark_notification_as_read(): void
    {
        $notification = Notification::create([
            'user_id' => $this->user->id,
            'type' => 'system',
            'title' => 'Thong bao',
            'message' => 'Noi dung',
            'is_read' => false,
        ]);

        $response = $this->postJson("/api/notifications/{$notification->id}/read", [], $this->withAuth());

        $response->assertStatus(200)
            ->assertJson(['status' => 'success', 'message' => 'Đánh dấu đã đọc thành công']);

        $this->assertDatabaseHas('notifications', ['id' => $notification->id, 'is_read' => true]);
    }

    public function test_can_mark_all_notifications_as_read(): void
    {
        Notification::create([
            'user_id' => $this->user->id,
            'type' => 'system',
            'title' => 'TB1',
            'message' => 'ND1',
            'is_read' => false,
        ]);
        Notification::create([
            'user_id' => $this->user->id,
            'type' => 'chat',
            'title' => 'TB2',
            'message' => 'ND2',
            'is_read' => false,
        ]);

        $response = $this->postJson('/api/notifications/read-all', [], $this->withAuth());

        $response->assertStatus(200)
            ->assertJson(['status' => 'success']);

        $this->assertDatabaseMissing('notifications', [
            'user_id' => $this->user->id,
            'is_read' => false,
        ]);
    }

    public function test_can_get_unread_notification_count(): void
    {
        Notification::create([
            'user_id' => $this->user->id,
            'type' => 'system',
            'title' => 'TB1',
            'message' => 'ND1',
            'is_read' => false,
        ]);
        Notification::create([
            'user_id' => $this->user->id,
            'type' => 'chat',
            'title' => 'TB2',
            'message' => 'ND2',
            'is_read' => true,
        ]);

        $response = $this->getJson('/api/notifications/unread-count', $this->withAuth());

        $response->assertStatus(200)
            ->assertJsonPath('data.unread_count', 1);
    }

    // ==================== AUTH ====================

    public function test_chat_endpoints_require_auth(): void
    {
        $response = $this->getJson('/api/chat');
        $response->assertStatus(401);
    }

    public function test_notification_endpoints_require_auth(): void
    {
        $response = $this->getJson('/api/notifications');
        $response->assertStatus(401);
    }
}