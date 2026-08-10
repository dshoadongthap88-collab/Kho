<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProjectsTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('projects')->truncate();
        
        $data = [
            [
                'id' => '1',
                'name' => 'HÓC MÔN',
                'code' => 'H1',
                'status' => 'active',
                'description' => null,
                'created_at' => '2026-06-22 00:50:24',
                'updated_at' => '2026-06-22 01:44:06',
            ],
            [
                'id' => '2',
                'name' => 'HẬU NGHĨA',
                'code' => 'H2',
                'status' => 'active',
                'description' => null,
                'created_at' => '2026-06-22 00:50:24',
                'updated_at' => '2026-06-22 01:44:06',
            ],
            [
                'id' => '3',
                'name' => 'CẦN GIỜ',
                'code' => 'H3',
                'status' => 'active',
                'description' => null,
                'created_at' => '2026-06-22 00:50:24',
                'updated_at' => '2026-06-22 01:44:06',
            ],
            [
                'id' => '4',
                'name' => 'CẦN GIUỘC',
                'code' => 'H4',
                'status' => 'active',
                'description' => null,
                'created_at' => '2026-06-22 00:50:24',
                'updated_at' => '2026-06-22 01:44:06',
            ],
            [
                'id' => '5',
                'name' => 'HR',
                'code' => 'HR',
                'status' => 'active',
                'description' => null,
                'created_at' => '2026-06-22 02:07:50',
                'updated_at' => '2026-06-22 02:07:50',
            ],
        ];
        
        foreach(array_chunk($data, 100) as $chunk) {
            DB::table('projects')->insert($chunk);
        }
    }
}
