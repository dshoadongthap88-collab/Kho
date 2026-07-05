<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class HouseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $houses = [
            ['code' => 'HOCMON', 'name' => 'Dự án Hóc Môn', 'is_master' => true, 'status' => 'active'],
            ['code' => 'HAUNGHIA', 'name' => 'Dự án Hậu Nghĩa', 'is_master' => false, 'status' => 'active'],
            ['code' => 'CANGIUOC', 'name' => 'Dự án Cần Giuộc', 'is_master' => false, 'status' => 'active'],
            ['code' => 'CANGIO', 'name' => 'Dự án Cần Giờ', 'is_master' => false, 'status' => 'active'],
            ['code' => 'HR', 'name' => 'House HR', 'is_master' => false, 'status' => 'active'],
        ];

        foreach ($houses as $house) {
            \App\Models\House::updateOrCreate(['code' => $house['code']], $house);
        }
    }
}
