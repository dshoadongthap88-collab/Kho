<?php

namespace App\Console\Commands;

use App\Models\Inventory;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Dọn dữ liệu cũ: chép inventories.warehouse_location sang products.location.
 *
 * Trước đây màn Tồn kho chỉ ghi vị trí vào bảng inventories, không ghi sang
 * products. Bảng Danh mục vật tư có fallback nên nhìn vẫn đúng, nhưng modal
 * Sửa và bản in đọc thẳng products.location nên thấy trống. Lệnh này đồng bộ
 * lại phần dữ liệu đã lệch từ trước.
 *
 * Chạy thử trước (không ghi gì):  php artisan inventory:sync-location
 * Ghi thật:                       php artisan inventory:sync-location --apply
 */
class SyncProductLocation extends Command
{
    protected $signature = 'inventory:sync-location
                            {--apply : Ghi thật vào CSDL, không có cờ này thì chỉ liệt kê}
                            {--house= : Chỉ xử lý một dự án (house_id)}';

    protected $description = 'Chép vị trí từ tồn kho sang danh mục vật tư cho các bản ghi đang lệch';

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');
        $house = $this->option('house');

        $query = Inventory::withoutGlobalScopes()
            ->whereNotNull('warehouse_location')
            ->where('warehouse_location', '!=', '');

        if ($house !== null) {
            $query->where('house_id', $house);
        }

        $lech = [];

        $query->orderBy('id')->chunkById(500, function ($rows) use (&$lech) {
            $products = Product::withoutGlobalScopes()
                ->whereIn('id', $rows->pluck('product_id')->all())
                ->get(['id', 'code', 'name', 'location'])
                ->keyBy('id');

            foreach ($rows as $inventory) {
                $product = $products->get($inventory->product_id);
                if (!$product) {
                    continue;
                }

                if ((string) $product->location !== (string) $inventory->warehouse_location) {
                    $lech[] = [
                        'product_id' => $product->id,
                        'code'       => $product->code,
                        'name'       => $product->name,
                        'cu'         => $product->location,
                        'moi'        => $inventory->warehouse_location,
                    ];
                }
            }
        });

        if (empty($lech)) {
            $this->info('Không có bản ghi nào lệch. Tồn kho và Danh mục vật tư đã khớp.');
            return self::SUCCESS;
        }

        $this->warn(sprintf('Tìm thấy %d vật tư có vị trí lệch giữa Tồn kho và Danh mục:', count($lech)));
        $this->table(
            ['Mã VT', 'Tên vật tư', 'Danh mục (cũ)', 'Tồn kho (mới)'],
            collect($lech)->take(20)->map(fn ($r) => [
                $r['code'],
                mb_substr((string) $r['name'], 0, 40),
                $r['cu'] ?? '(trống)',
                $r['moi'],
            ])->all()
        );

        if (count($lech) > 20) {
            $this->line(sprintf('   ... và %d dòng nữa.', count($lech) - 20));
        }

        if (!$apply) {
            $this->newLine();
            $this->info('Đây là chạy thử, chưa ghi gì. Thêm --apply để ghi thật.');
            return self::SUCCESS;
        }

        DB::transaction(function () use ($lech) {
            foreach (array_chunk($lech, 500) as $chunk) {
                foreach ($chunk as $row) {
                    Product::withoutGlobalScopes()
                        ->where('id', $row['product_id'])
                        ->update(['location' => $row['moi']]);
                }
            }
        });

        $this->newLine();
        $this->info(sprintf('Đã đồng bộ vị trí cho %d vật tư sang Danh mục vật tư.', count($lech)));

        return self::SUCCESS;
    }
}
