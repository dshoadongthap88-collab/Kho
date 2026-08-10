<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoriesTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('categories')->truncate();
        
        $data = [
            [
                'id' => '1',
                'name' => 'Nguyên vật liệu',
                'slug' => 'nguyen-vat-lieu',
                'description' => 'Danh mục tự động tạo để minh họa biểu đồ',
                'status' => 'active',
                'created_at' => '2026-06-22 01:00:09',
                'updated_at' => '2026-06-22 01:00:09',
                'house_id' => null,
            ],
            [
                'id' => '2',
                'name' => 'Phụ gia',
                'slug' => 'phu-gia',
                'description' => 'Danh mục tự động tạo để minh họa biểu đồ',
                'status' => 'active',
                'created_at' => '2026-06-22 01:00:09',
                'updated_at' => '2026-06-22 01:00:09',
                'house_id' => null,
            ],
            [
                'id' => '3',
                'name' => 'Bao bì',
                'slug' => 'bao-bi',
                'description' => 'Danh mục tự động tạo để minh họa biểu đồ',
                'status' => 'active',
                'created_at' => '2026-06-22 01:00:09',
                'updated_at' => '2026-06-22 01:00:09',
                'house_id' => null,
            ],
            [
                'id' => '4',
                'name' => 'Thành phẩm',
                'slug' => 'thanh-pham',
                'description' => 'Danh mục tự động tạo để minh họa biểu đồ',
                'status' => 'active',
                'created_at' => '2026-06-22 01:00:09',
                'updated_at' => '2026-06-22 01:00:09',
                'house_id' => null,
            ],
            [
                'id' => '5',
                'name' => 'Vật tư tiêu hao',
                'slug' => 'vat-tu-tieu-hao',
                'description' => 'Danh mục tự động tạo để minh họa biểu đồ',
                'status' => 'active',
                'created_at' => '2026-06-22 01:00:09',
                'updated_at' => '2026-06-22 01:00:09',
                'house_id' => null,
            ],
            [
                'id' => '6',
                'name' => 'Linh kiện',
                'slug' => 'linh-kien',
                'description' => 'Danh mục tự động tạo để minh họa biểu đồ',
                'status' => 'active',
                'created_at' => '2026-06-22 01:00:09',
                'updated_at' => '2026-06-22 01:00:09',
                'house_id' => null,
            ],
            [
                'id' => '7',
                'name' => 'Nguyên vật liệu chính',
                'slug' => 'nguyen-vat-lieu-chinh',
                'description' => null,
                'status' => 'active',
                'created_at' => '2026-06-22 01:00:10',
                'updated_at' => '2026-06-22 01:00:10',
                'house_id' => null,
            ],
        ];
        
        foreach(array_chunk($data, 100) as $chunk) {
            DB::table('categories')->insert($chunk);
        }
    }
}
