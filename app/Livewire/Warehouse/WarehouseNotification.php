<?php

namespace App\Livewire\Warehouse;

use Livewire\Component;
use Illuminate\Support\Facades\DB;

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
            DB::table('chat_messages')
                ->where('is_read', false)
                ->update(['is_read' => true, 'updated_at' => now()]);
        } catch (\Exception $e) {
            // Ignore error
        }
    }

    public function getUnreadCountProperty()
    {
        try {
            return DB::table('chat_messages')
                ->where('is_read', false)
                ->count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    public function getMessagesProperty()
    {
        try {
            return DB::table('chat_messages')
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
