<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SuppliersTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('suppliers')->truncate();
        
        $data = [
            [
                'id' => '1',
                'name' => 'NCC TÙNG ANH',
                'address' => null,
                'phone' => '0123566866',
                'contact_person' => 'TÙNG TÁO',
                'email' => null,
                'department' => null,
                'type' => 'supplier',
                'created_at' => '2026-06-27 23:50:44',
                'updated_at' => '2026-06-27 23:50:44',
                'status' => 'active',
                'total_debt' => '0.00',
                'deleted_at' => null,
            ],
            [
                'id' => '2',
                'name' => 'NCC THU LIÊN',
                'address' => null,
                'phone' => null,
                'contact_person' => 'THU LIÊN',
                'email' => null,
                'department' => null,
                'type' => 'supplier',
                'created_at' => '2026-06-28 12:26:04',
                'updated_at' => '2026-06-28 12:26:04',
                'status' => 'active',
                'total_debt' => '0.00',
                'deleted_at' => null,
            ],
            [
                'id' => '3',
                'name' => 'NCC FULI VN',
                'address' => null,
                'phone' => '0376616547',
                'contact_person' => 'Dương Thị Hồng Phượng',
                'email' => null,
                'department' => null,
                'type' => 'supplier',
                'created_at' => '2026-08-10 13:12:22',
                'updated_at' => '2026-08-10 13:12:22',
                'status' => 'active',
                'total_debt' => '0.00',
                'deleted_at' => null,
            ],
        ];
        
        foreach(array_chunk($data, 100) as $chunk) {
            DB::table('suppliers')->insert($chunk);
        }
    }
}
