<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MaintenanceBomsTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('maintenance_boms')->truncate();
        
        $data = [
            [
                'id' => '1',
                'bom_code' => 'MBOM-2VJDZUKV',
                'asset_id' => '7',
                'maintenance_level' => '250 giờ',
                'cycle' => '250',
                'created_by' => '3',
                'created_at' => '2026-07-13 15:06:48',
                'updated_at' => '2026-07-13 15:06:48',
                'house_id' => '1',
            ],
            [
                'id' => '2',
                'bom_code' => 'MBOM-EDPDPQWT',
                'asset_id' => '7',
                'maintenance_level' => '500 giờ',
                'cycle' => '500',
                'created_by' => '3',
                'created_at' => '2026-07-13 15:10:22',
                'updated_at' => '2026-07-13 15:10:22',
                'house_id' => '1',
            ],
            [
                'id' => '3',
                'bom_code' => 'BOM-YX66HP',
                'asset_id' => '8',
                'maintenance_level' => '500',
                'cycle' => '500',
                'created_by' => '3',
                'created_at' => '2026-08-03 02:16:45',
                'updated_at' => '2026-08-03 02:16:45',
                'house_id' => null,
            ],
            [
                'id' => '4',
                'bom_code' => 'BOM-4VRNG8',
                'asset_id' => '8',
                'maintenance_level' => '40000',
                'cycle' => '500',
                'created_by' => '3',
                'created_at' => '2026-08-03 02:19:15',
                'updated_at' => '2026-08-03 02:19:15',
                'house_id' => null,
            ],
            [
                'id' => '6',
                'bom_code' => 'BOM-SYCAGN',
                'asset_id' => '9',
                'maintenance_level' => '500',
                'cycle' => '500',
                'created_by' => '3',
                'created_at' => '2026-08-03 09:47:27',
                'updated_at' => '2026-08-03 09:47:27',
                'house_id' => null,
            ],
            [
                'id' => '7',
                'bom_code' => 'BOM-EUYGIP',
                'asset_id' => '9',
                'maintenance_level' => '2000',
                'cycle' => '500',
                'created_by' => '1',
                'created_at' => '2026-08-10 04:46:20',
                'updated_at' => '2026-08-10 04:46:20',
                'house_id' => null,
            ],
            [
                'id' => '8',
                'bom_code' => 'BOM-BYO90F',
                'asset_id' => '9',
                'maintenance_level' => '4000',
                'cycle' => '500',
                'created_by' => '1',
                'created_at' => '2026-08-10 04:51:38',
                'updated_at' => '2026-08-10 04:51:38',
                'house_id' => null,
            ],
        ];
        
        foreach(array_chunk($data, 100) as $chunk) {
            DB::table('maintenance_boms')->insert($chunk);
        }
    }
}
