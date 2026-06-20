<?php

namespace App\Livewire\Hr;

use App\Models\Notification;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class NotificationManager extends Component
{
    use WithPagination;

    public $search = '';
    
    // Form fields
    public $notification_id = null;
    public $user_id = '';
    public $title = '';
    public $message = '';
    public $isModalOpen = false;

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function openModal()
    {
        $this->resetValidation();
        $this->isModalOpen = true;
    }

    public function closeModal()
    {
        $this->isModalOpen = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->notification_id = null;
        $this->user_id = '';
        $this->title = '';
        $this->message = '';
    }

    public function create()
    {
        $this->resetForm();
        $this->openModal();
    }

    public function edit($id)
    {
        $notification = Notification::findOrFail($id);
        $this->notification_id = $notification->id;
        $this->user_id = $notification->user_id;
        $this->title = $notification->title;
        $this->message = $notification->message;

        $this->openModal();
    }

    public function save()
    {
        $this->validate([
            'user_id' => 'required',
            'title' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        if ($this->user_id === 'all') {
            $users = User::all();
            foreach ($users as $user) {
                Notification::create([
                    'user_id' => $user->id,
                    'type' => 'system',
                    'title' => $this->title,
                    'message' => $this->message,
                    'is_read' => false,
                ]);
            }
            session()->flash('message', 'Đã gửi thông báo đến tất cả người dùng thành công.');
        } else {
            Notification::updateOrCreate(
                ['id' => $this->notification_id],
                [
                    'user_id' => $this->user_id,
                    'type' => 'system',
                    'title' => $this->title,
                    'message' => $this->message,
                    'is_read' => false,
                ]
            );
            session()->flash('message', $this->notification_id ? 'Cập nhật thông báo thành công.' : 'Tạo thông báo thành công.');
        }

        $this->closeModal();
    }

    public function delete($id)
    {
        Notification::findOrFail($id)->delete();
        session()->flash('message', 'Xóa thông báo thành công.');
    }

    public function render()
    {
        $notifications = Notification::with('user')
            ->where('title', 'like', "%{$this->search}%")
            ->orWhere('message', 'like', "%{$this->search}%")
            ->orWhereHas('user', function($q) {
                $q->where('name', 'like', "%{$this->search}%");
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $users = User::orderBy('name')->get();

        return view('livewire.hr.notification-manager', [
            'notifications' => $notifications,
            'users' => $users,
        ])->layout('components.warehouse-layout', ['title' => 'Quản lý Thông báo']);
    }
}
