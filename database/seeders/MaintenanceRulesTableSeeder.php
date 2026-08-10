<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MaintenanceRulesTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('maintenance_rules')->truncate();
        
        $data = [
            [
                'id' => '1',
                'rule_code' => 'BD250',
                'name' => 'Bảo dưỡng cấp 1',
                'machine_type' => 'Xe Lu, xe ben, xe ủi, xe xúc',
                'category' => 'thay nhớt',
                'cycle_km' => '500.00',
                'cycle_hours' => '250.00',
                'cycle_months' => '0',
                'content' => '',
                'material_needed' => '[\"Thay nhớt 15w40\"]',
                'estimated_time' => '0.00',
                'notes' => '',
                'created_by' => 'Admin',
                'created_at' => '2026-06-27 22:24:26',
                'updated_at' => '2026-08-02 09:59:44',
                'deleted_at' => '2026-08-02 09:59:44',
                'house_id' => '1',
            ],
        ];
        
        foreach(array_chunk($data, 100) as $chunk) {
            DB::table('maintenance_rules')->insert($chunk);
        }
    }
}
