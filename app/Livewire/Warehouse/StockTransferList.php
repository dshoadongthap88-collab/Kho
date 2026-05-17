<?php

namespace App\Livewire\Warehouse;

use App\Models\StockTransfer;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Config;

class StockTransferList extends Component
{
    use WithPagination;

    public $search = '';
    public $chatMessage = '';

    public static function broadcastMessage($senderName, $message, $type = 'user', $senderId = null)
    {
        $currentHouse = session('current_house', 1);

        // Loop qua cả 4 nhà để phát tin nhắn/thông báo đồng bộ
        for ($h = 1; $h <= 4; $h++) {
            try {
                $dbName = $h == 1 ? 'laravel' : 'laravel_' . $h;
                
                // Sử dụng lệnh USE của MySQL để chuyển đổi Database.
                // Nếu DB không tồn tại, câu lệnh sẽ throw exception lập tức và nhảy sang catch,
                // do đó sẽ KHÔNG bao giờ bị ghi đúp hay ghi nhầm sang DB mặc định (laravel) nhiều lần.
                DB::statement("USE `{$dbName}`");

                // Tạo bảng tự động nếu chưa tồn tại
                if (!Schema::hasTable('chat_messages')) {
                    Schema::create('chat_messages', function ($table) {
                        $table->id();
                        $table->string('sender_name');
                        $table->integer('sender_id')->nullable();
                        $table->text('message');
                        $table->string('type')->default('user'); // user, system
                        $table->timestamps();
                    });
                }

                // Ghi nhận tin nhắn
                DB::table('chat_messages')->insert([
                    'sender_name' => $senderName,
                    'sender_id' => $senderId,
                    'message' => $message,
                    'type' => $type,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } catch (\Exception $e) {
                // Bỏ qua nếu nhà kho chưa được cài đặt CSDL
            }
        }

        // Khôi phục kết nối gốc về nhà hiện tại
        try {
            $currentDb = $currentHouse == 1 ? 'laravel' : 'laravel_' . $currentHouse;
            DB::statement("USE `{$currentDb}`");
        } catch (\Exception $e) {
            // Im lặng
        }
    }

    public function sendMessage()
    {
        $msg = trim($this->chatMessage);
        if (empty($msg)) {
            return;
        }

        $senderName = auth()->user()->name ?? 'Nhân viên';
        $senderId = auth()->id();

        self::broadcastMessage($senderName, $msg, 'user', $senderId);

        $this->chatMessage = '';
    }

    public function render()
    {
        // Đảm bảo bảng tồn tại ở nhà hiện tại
        if (!Schema::hasTable('chat_messages')) {
            try {
                Schema::create('chat_messages', function ($table) {
                    $table->id();
                    $table->string('sender_name');
                    $table->integer('sender_id')->nullable();
                    $table->text('message');
                    $table->string('type')->default('user');
                    $table->timestamps();
                });
            } catch (\Exception $e) {
                // Im lặng nếu gặp lỗi tạo bảng
            }
        }

        $transfers = StockTransfer::with(['creator', 'items'])
            ->where('transfer_code', 'like', '%' . $this->search . '%')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Lấy 30 tin nhắn/thông báo gần nhất
        $messages = [];
        try {
            $messages = DB::table('chat_messages')
                ->orderBy('created_at', 'desc')
                ->limit(30)
                ->get()
                ->reverse()
                ->toArray();
        } catch (\Exception $e) {
            // Im lặng
        }

        return view('livewire.warehouse.stock-transfer-list', [
            'transfers' => $transfers,
            'messages' => $messages
        ])->layout('components.warehouse-layout', ['title' => 'Lịch sử chuyển kho & Thông báo chung']);
    }
}
