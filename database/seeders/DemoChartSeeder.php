<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Category;
use App\Models\Product;
use App\Models\Inventory;
use App\Models\InventoryTransaction;

class DemoChartSeeder extends Seeder
{
    public function run()
    {
        $categories = ['Nguyên vật liệu', 'Phụ gia', 'Bao bì', 'Thành phẩm', 'Vật tư tiêu hao', 'Linh kiện'];
        foreach($categories as $cat) {
            Category::firstOrCreate(['name' => $cat], ['slug' => Str::slug($cat), 'description' => 'Danh mục tự động tạo để minh họa biểu đồ']);
        }

        $cats = Category::all();

        // Cập nhật category cho các sản phẩm hiện tại nếu chưa có
        $existing = Product::whereNull('category_id')->get();
        foreach($existing as $p) {
            $p->update(['category_id' => $cats->random()->id, 'expiry_date' => now()->addDays(rand(-30, 200))]);
        }

        // Tạo thêm sản phẩm để đảm bảo có đủ dữ liệu vẽ các loại biểu đồ
        for($i = 1; $i <= 30; $i++) {
            $p = Product::firstOrCreate(
                ['code' => 'SP-DEMO-' . str_pad($i, 3, '0', STR_PAD_LEFT)],
                [
                    'name' => 'Sản phẩm demo ' . $i,
                    'category_id' => $cats->random()->id,
                    'unit' => collect(['kg', 'thùng', 'cái', 'lít', 'hộp'])->random(),
                    'price' => rand(10, 500) * 1000,
                    'min_stock' => rand(10, 100),
                    // Tạo một số sp có hạn sử dụng quá hạn, cận date và bình thường để lên màu Heatmap
                    'expiry_date' => now()->addDays(rand(-60, 180)),
                ]
            );
            
            // Xóa inventory và tx cũ của demo này nếu đã chạy
            Inventory::where('product_id', $p->id)->delete();
            InventoryTransaction::where('product_id', $p->id)->delete();

            $inv = Inventory::create([
                'product_id' => $p->id,
                'quantity' => rand(0, 1500),
                'warehouse_location' => 'Khu ' . collect(['A', 'B', 'C', 'D'])->random(),
            ]);

            // Giao dịch
            $txCount = rand(3, 10);
            for($j=0; $j<$txCount; $j++) {
                $type = collect(['import', 'import', 'export', 'adjust'])->random();
                $qty = rand(10, 200);
                if ($type == 'export') $qty = -$qty;
                
                // Mở rộng thời gian để có hàng tồn đọng lâu (> 90 days không giao dịch)
                // Hoặc giao dịch gầy đây (1-80 days)
                // Ép một số sản phẩm chỉ có giao dịch rất cũ (> 100 days)
                $daysAgo = ($i % 5 == 0) ? rand(100, 150) : rand(1, 60); 
                
                InventoryTransaction::create([
                    'product_id' => $p->id,
                    'type' => $type,
                    'quantity' => $qty,
                    'note' => 'Giao dịch sinh tự động minh họa',
                    'created_at' => now()->subDays($daysAgo),
                    'updated_at' => now()->subDays($daysAgo),
                ]);
            }
        }
    }
}
