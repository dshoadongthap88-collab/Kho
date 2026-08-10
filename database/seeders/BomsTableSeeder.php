<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BomsTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('boms')->truncate();
        
        $data = [
            [
                'id' => '1',
                'product_id' => '31',
                'material_id' => '33',
                'quantity' => '0.500',
                'unit' => 'kg',
                'created_at' => '2026-06-22 01:00:10',
                'updated_at' => '2026-06-22 01:00:10',
            ],
            [
                'id' => '2',
                'product_id' => '31',
                'material_id' => '34',
                'quantity' => '0.200',
                'unit' => 'kg',
                'created_at' => '2026-06-22 01:00:10',
                'updated_at' => '2026-06-22 01:00:10',
            ],
            [
                'id' => '3',
                'product_id' => '31',
                'material_id' => '35',
                'quantity' => '0.100',
                'unit' => 'lít',
                'created_at' => '2026-06-22 01:00:10',
                'updated_at' => '2026-06-22 01:00:10',
            ],
            [
                'id' => '4',
                'product_id' => '31',
                'material_id' => '37',
                'quantity' => '0.200',
                'unit' => 'lít',
                'created_at' => '2026-06-22 01:00:10',
                'updated_at' => '2026-06-22 01:00:10',
            ],
            [
                'id' => '5',
                'product_id' => '32',
                'material_id' => '36',
                'quantity' => '0.800',
                'unit' => 'kg',
                'created_at' => '2026-06-22 01:00:10',
                'updated_at' => '2026-06-22 01:00:10',
            ],
            [
                'id' => '6',
                'product_id' => '32',
                'material_id' => '34',
                'quantity' => '0.100',
                'unit' => 'kg',
                'created_at' => '2026-06-22 01:00:10',
                'updated_at' => '2026-06-22 01:00:10',
            ],
            [
                'id' => '7',
                'product_id' => '32',
                'material_id' => '37',
                'quantity' => '0.300',
                'unit' => 'lít',
                'created_at' => '2026-06-22 01:00:10',
                'updated_at' => '2026-06-22 01:00:10',
            ],
        ];
        
        foreach(array_chunk($data, 100) as $chunk) {
            DB::table('boms')->insert($chunk);
        }
    }
}
