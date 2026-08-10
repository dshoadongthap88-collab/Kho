<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StockTransferItemsTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('stock_transfer_items')->truncate();
        
        $data = [
            [
                'id' => '1',
                'stock_transfer_id' => '1',
                'product_code' => 'SP-DEMO-030',
                'product_id' => null,
                'product_name' => null,
                'quantity' => '100.00',
                'unit' => null,
                'note' => '',
                'created_at' => '2026-06-24 14:09:44',
                'updated_at' => '2026-06-24 14:09:44',
                'location' => 'Khu A',
            ],
            [
                'id' => '2',
                'stock_transfer_id' => '2',
                'product_code' => 'NVL-005',
                'product_id' => null,
                'product_name' => null,
                'quantity' => '1.00',
                'unit' => null,
                'note' => '',
                'created_at' => '2026-06-28 12:31:04',
                'updated_at' => '2026-06-28 12:31:04',
                'location' => 'A2',
            ],
        ];
        
        foreach(array_chunk($data, 100) as $chunk) {
            DB::table('stock_transfer_items')->insert($chunk);
        }
    }
}
