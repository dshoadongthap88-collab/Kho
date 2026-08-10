<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HousesTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('houses')->truncate();
        
        $data = [
            [
                'id' => '1',
                'code' => 'HOCMON',
                'name' => 'Dự án Hóc Môn',
                'is_master' => '1',
                'status' => 'active',
                'created_at' => '2026-07-05 15:19:56',
                'updated_at' => '2026-07-05 15:19:56',
            ],
            [
                'id' => '2',
                'code' => 'HAUNGHIA',
                'name' => 'Dự án Hậu Nghĩa',
                'is_master' => '0',
                'status' => 'active',
                'created_at' => '2026-07-05 15:19:56',
                'updated_at' => '2026-07-05 15:19:56',
            ],
            [
                'id' => '3',
                'code' => 'CANGIUOC',
                'name' => 'Dự án Cần Giuộc',
                'is_master' => '0',
                'status' => 'active',
                'created_at' => '2026-07-05 15:19:56',
                'updated_at' => '2026-07-05 15:19:56',
            ],
            [
                'id' => '4',
                'code' => 'CANGIO',
                'name' => 'Dự án Cần Giờ',
                'is_master' => '0',
                'status' => 'active',
                'created_at' => '2026-07-05 15:19:56',
                'updated_at' => '2026-07-05 15:19:56',
            ],
            [
                'id' => '5',
                'code' => 'HR',
                'name' => 'House HR',
                'is_master' => '0',
                'status' => 'active',
                'created_at' => '2026-07-05 15:19:56',
                'updated_at' => '2026-07-05 15:19:56',
            ],
        ];
        
        foreach(array_chunk($data, 100) as $chunk) {
            DB::table('houses')->insert($chunk);
        }
    }
}
