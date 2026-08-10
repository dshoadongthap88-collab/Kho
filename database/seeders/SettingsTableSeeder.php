<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingsTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('settings')->truncate();
        
        $data = [
            [
                'id' => '1',
                'key' => 'auto_daily_odo_enabled',
                'value' => 'true',
                'created_at' => '2026-07-05 16:49:45',
                'updated_at' => '2026-07-05 17:18:23',
            ],
        ];
        
        foreach(array_chunk($data, 100) as $chunk) {
            DB::table('settings')->insert($chunk);
        }
    }
}
