<?php

namespace App\Livewire\Warehouse;

use Livewire\Component;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;

class WarehouseNotification extends Component
{
    public bool $open = false;

    public function toggle()
    {
        $this->open = !$this->open;

        if ($this->open) {
            $this->markAsRead();
        }
    }

    public function markAsRead()
    {
        try {
            if (Auth::check()) {
                Notification::where('user_id', Auth::id())
                    ->where('is_read', false)
                    ->update(['is_read' => true, 'updated_at' => now()]);
            }
        } catch (\Exception $e) {
            // Ignore error
        }
    }

    public function getUnreadCountProperty()
    {
        try {
            if (!Auth::check()) return 0;
            return Notification::where('user_id', Auth::id())
                ->where('is_read', false)
                ->count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    public function getMessagesProperty()
    {
        try {
            if (!Auth::check()) return collect([]);
            return Notification::where('user_id', Auth::id())
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();
        } catch (\Exception $e) {
            return collect([]);
        }
    }

    public function render()
    {
        return view('livewire.warehouse.warehouse-notification');
    }
}
