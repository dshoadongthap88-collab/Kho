<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StockTransfersTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('stock_transfers')->truncate();
        
        $data = [
            [
                'id' => '1',
                'transfer_code' => 'TF-20260624-3382',
                'transfer_date' => '2026-06-24',
                'from_warehouse_id' => null,
                'to_warehouse_id' => null,
                'status' => 'pending',
                'confirmed_by' => null,
                'confirmed_at' => null,
                'cancelled_by' => null,
                'cancelled_at' => null,
                'note' => '',
                'reject_reason' => null,
                'created_by' => '3',
                'created_at' => '2026-06-24 14:09:44',
                'updated_at' => '2026-06-24 14:09:44',
                'sender_phone' => '0708091050',
                'receiver_id' => '3',
                'receiver_phone' => '0708091050',
                'from_project_id' => '1',
                'to_project_id' => '3',
                'house_id' => null,
            ],
            [
                'id' => '2',
                'transfer_code' => 'TF-20260628-5546',
                'transfer_date' => '2026-06-28',
                'from_warehouse_id' => null,
                'to_warehouse_id' => null,
                'status' => 'pending',
                'confirmed_by' => null,
                'confirmed_at' => null,
                'cancelled_by' => null,
                'cancelled_at' => null,
                'note' => '',
                'reject_reason' => null,
                'created_by' => '3',
                'created_at' => '2026-06-28 12:31:04',
                'updated_at' => '2026-06-28 12:31:04',
                'sender_phone' => '0708091050',
                'receiver_id' => '3',
                'receiver_phone' => '0708091050',
                'from_project_id' => '1',
                'to_project_id' => '2',
                'house_id' => null,
            ],
        ];
        
        foreach(array_chunk($data, 100) as $chunk) {
            DB::table('stock_transfers')->insert($chunk);
        }
    }
}
