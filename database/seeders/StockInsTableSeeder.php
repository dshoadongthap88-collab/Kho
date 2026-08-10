<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StockInsTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('stock_ins')->truncate();
        
        $data = [
            [
                'id' => '1',
                'code' => 'SI-20260627-0001',
                'supplier_name' => 'NCC TÙNG ANH',
                'type' => 'purchase_produced',
                'stock_in_date' => '2026-06-27',
                'marked_received' => '0',
                'received_at' => null,
                'manufacturer' => '',
                'status' => 'completed',
                'note' => '',
                'created_by' => '3',
                'created_at' => '2026-06-27 23:52:14',
                'updated_at' => '2026-06-27 23:52:14',
                'house_id' => null,
            ],
            [
                'id' => '2',
                'code' => 'SI-20260713-0001',
                'supplier_name' => '',
                'type' => 'purchase_produced',
                'stock_in_date' => '2026-07-13',
                'marked_received' => '0',
                'received_at' => null,
                'manufacturer' => '',
                'status' => 'completed',
                'note' => '',
                'created_by' => '3',
                'created_at' => '2026-07-13 15:01:51',
                'updated_at' => '2026-07-13 15:01:51',
                'house_id' => '1',
            ],
            [
                'id' => '3',
                'code' => 'SI-20260725-0002',
                'supplier_name' => 'NCC TÙNG ANH',
                'type' => 'purchase_produced',
                'stock_in_date' => '2026-07-25',
                'marked_received' => '0',
                'received_at' => null,
                'manufacturer' => 'SANY',
                'status' => 'completed',
                'note' => '',
                'created_by' => '3',
                'created_at' => '2026-07-25 02:27:23',
                'updated_at' => '2026-07-25 02:27:23',
                'house_id' => '1',
            ],
            [
                'id' => '8',
                'code' => 'SI-20260809-0003',
                'supplier_name' => 'NCC THU LIÊN',
                'type' => 'purchase_produced',
                'stock_in_date' => '2026-08-09',
                'marked_received' => '0',
                'received_at' => null,
                'manufacturer' => '',
                'status' => 'completed',
                'note' => '',
                'created_by' => '1',
                'created_at' => '2026-08-09 02:31:56',
                'updated_at' => '2026-08-09 02:31:56',
                'house_id' => '1',
            ],
        ];
        
        foreach(array_chunk($data, 100) as $chunk) {
            DB::table('stock_ins')->insert($chunk);
        }
    }
}
