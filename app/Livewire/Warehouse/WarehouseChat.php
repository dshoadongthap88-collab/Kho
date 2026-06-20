<?php

namespace App\Livewire\Warehouse;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\ChatMessage;
use Illuminate\Support\Facades\Auth;

class WarehouseChat extends Component
{
    use WithFileUploads;

    public $message = '';
    public $image;
    public $reply_to_id = null;

    public function mount()
    {
        // Update user's last read timestamp
        if (Auth::check()) {
            Auth::user()->forceFill(['last_read_chat_at' => now()])->save();
        }
    }

    public function setReply($messageId)
    {
        $this->reply_to_id = $messageId;
    }

    public function cancelReply()
    {
        $this->reply_to_id = null;
    }

    public function sendMessage()
    {
        $this->validate([
            'message' => 'required_without:image|string',
            'image' => 'nullable|image|max:5120', // 5MB max
        ]);

        $imagePath = null;
        if ($this->image) {
            $imagePath = $this->image->store('chat_images', 'public');
        }

        ChatMessage::create([
            'user_id' => Auth::id(),
            'type' => $this->image ? 'image' : 'text',
            'content' => $this->message,
            'attachment_path' => $imagePath,
            'is_read' => true, // Auto read by sender
            'reply_to_id' => $this->reply_to_id,
        ]);

        $this->message = '';
        $this->image = null;
        $this->reply_to_id = null;
        
        if (Auth::check()) {
            Auth::user()->forceFill(['last_read_chat_at' => now()])->save();
        }

        $this->dispatch('message-sent');
    }

    public function render()
    {
        $messages = ChatMessage::with(['sender', 'repliedMessage.sender'])
            ->orderBy('created_at', 'asc')
            ->get();

        $activeUsers = \App\Models\User::where('status', 'active')->get(['id', 'name', 'username']);

        return view('livewire.warehouse.warehouse-chat', [
            'messages' => $messages,
            'activeUsers' => $activeUsers,
        ])->layout('components.warehouse-layout', ['title' => 'Trò chuyện hệ thống']);
    }
}
