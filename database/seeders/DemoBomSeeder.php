<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;
use App\Models\Bom;

class DemoBomSeeder extends Seeder
{
    public function run()
    {
        // 1. Lọc hoặc tạo Category Thành phẩm
        $catThanhPham = Category::firstOrCreate(['name' => 'Thành phẩm'], ['slug' => 'thanh-pham']);
        $catNguyenLieu = Category::firstOrCreate(['name' => 'Nguyên vật liệu chính'], ['slug' => 'nguyen-vat-lieu-chinh']);
        $catPhuGia = Category::firstOrCreate(['name' => 'Phụ gia'], ['slug' => 'phu-gia']);

        // 2. Định nghĩa Thành phẩm
        $gaRan = Product::firstOrCreate(
            ['code' => 'TP-001'],
            ['name' => 'Set Đùi gà rán BBQ', 'category_id' => $catThanhPham->id, 'unit' => 'Phần', 'price' => 150000, 'min_stock' => 50, 'status' => 'active']
        );
        $khoaiTay = Product::firstOrCreate(
            ['code' => 'TP-002'],
            ['name' => 'Phần Khoai tây lốc xoáy', 'category_id' => $catThanhPham->id, 'unit' => 'Phần', 'price' => 50000, 'min_stock' => 100, 'status' => 'active']
        );

        // 3. Định nghĩa Nguyên liệu
        $thitGa = Product::firstOrCreate(['code' => 'NVL-001'], ['name' => 'Thịt gà đùi tươi', 'category_id' => $catNguyenLieu->id, 'unit' => 'kg', 'price' => 50000, 'status' => 'active']);
        $botChien = Product::firstOrCreate(['code' => 'NVL-002'], ['name' => 'Bột chiên xù xốp', 'category_id' => $catPhuGia->id, 'unit' => 'kg', 'price' => 20000, 'status' => 'active']);
        $nuocSot = Product::firstOrCreate(['code' => 'NVL-003'], ['name' => 'Nước sốt BBQ Hàn Quốc', 'category_id' => $catPhuGia->id, 'unit' => 'lít', 'price' => 80000, 'status' => 'active']);
        
        $khoaiTuoi = Product::firstOrCreate(['code' => 'NVL-004'], ['name' => 'Khoai tây tươi Đà Lạt', 'category_id' => $catNguyenLieu->id, 'unit' => 'kg', 'price' => 25000, 'status' => 'active']);
        $dauAn = Product::firstOrCreate(['code' => 'NVL-005'], ['name' => 'Dầu đậu nành', 'category_id' => $catPhuGia->id, 'unit' => 'lít', 'price' => 55000, 'status' => 'active']);

        // 4. Tạo cấu trúc BOM (Định mức tiêu hao)
        // Set Gà: 0.5kg thịt, 0.2kg bột, 0.1L nước sốt, 0.2L dầu
        Bom::firstOrCreate(['product_id' => $gaRan->id, 'material_id' => $thitGa->id], ['quantity' => 0.5, 'unit' => 'kg']);
        Bom::firstOrCreate(['product_id' => $gaRan->id, 'material_id' => $botChien->id], ['quantity' => 0.2, 'unit' => 'kg']);
        Bom::firstOrCreate(['product_id' => $gaRan->id, 'material_id' => $nuocSot->id], ['quantity' => 0.1, 'unit' => 'lít']);
        Bom::firstOrCreate(['product_id' => $gaRan->id, 'material_id' => $dauAn->id], ['quantity' => 0.2, 'unit' => 'lít']);

        // Set Khoai tây: 0.8kg khoai, 0.1kg bột, 0.3L dầu
        Bom::firstOrCreate(['product_id' => $khoaiTay->id, 'material_id' => $khoaiTuoi->id], ['quantity' => 0.8, 'unit' => 'kg']);
        Bom::firstOrCreate(['product_id' => $khoaiTay->id, 'material_id' => $botChien->id], ['quantity' => 0.1, 'unit' => 'kg']);
        Bom::firstOrCreate(['product_id' => $khoaiTay->id, 'material_id' => $dauAn->id], ['quantity' => 0.3, 'unit' => 'lít']);
    }
}
