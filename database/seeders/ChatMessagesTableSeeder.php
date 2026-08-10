<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ChatMessagesTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('chat_messages')->truncate();
        
        $data = [
            [
                'id' => '1',
                'user_id' => '3',
                'recipient_id' => null,
                'type' => 'system',
                'is_read' => '0',
                'content' => '🔔 [CHUYỂN KHO] Kho HÓC MÔN vừa tạo phiếu chuyển hàng tới Kho CẦN GIỜ. Mã phiếu: TF-20260624-3382',
                'attachment_path' => null,
                'created_at' => '2026-06-24 14:09:44',
                'updated_at' => '2026-06-24 14:09:44',
                'reply_to_id' => null,
            ],
            [
                'id' => '2',
                'user_id' => '3',
                'recipient_id' => null,
                'type' => 'system',
                'is_read' => '0',
                'content' => '🔔 [CHUYỂN KHO] Kho HÓC MÔN vừa tạo phiếu chuyển hàng tới Kho HẬU NGHĨA. Mã phiếu: TF-20260628-5546',
                'attachment_path' => null,
                'created_at' => '2026-06-28 12:31:04',
                'updated_at' => '2026-06-28 12:31:04',
                'reply_to_id' => null,
            ],
        ];
        
        foreach(array_chunk($data, 100) as $chunk) {
            DB::table('chat_messages')->insert($chunk);
        }
    }
}
