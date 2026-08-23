<?php

namespace App\Traits;

use App\Models\Inventory;
use App\Models\Product;

/**
 * Giúp các luồng import chỉ đụng vào dữ liệu của ĐÚNG dự án đang đứng.
 *
 * Trước đây các import dùng withoutGlobalScope('house') nên khi đang ở HẬU NGHĨA
 * mà nhập Excel, nó tìm thấy mã vật tư của HÓC MÔN và sửa luôn dữ liệu của
 * HÓC MÔN — còn HẬU NGHĨA thì không được tạo bản ghi nào.
 *
 * Bản ghi cũ chưa gắn house_id (house_id = null) vẫn được tái sử dụng để không
 * tạo ra bản ghi trùng, nhưng KHÔNG tự gán lại house_id cho chúng — việc dọn dữ
 * liệu mồ côi phải là một thao tác riêng, không phải tác dụng phụ của import.
 */
trait ResolvesHouseScopedRecords
{
    private function currentHouseId()
    {
        return session('current_house') ?? (auth()->user()?->current_house_id);
    }

    private function findProductForCurrentHouse(string $code): ?Product
    {
        if ($product = Product::where('code', $code)->first()) {
            return $product;
        }

        if (!$this->currentHouseId()) {
            return null;
        }

        return Product::withoutGlobalScope('house')
            ->where('code', $code)
            ->whereNull('house_id')
            ->first();
    }

    private function findInventoryForCurrentHouse(int $productId): ?Inventory
    {
        if ($inventory = Inventory::where('product_id', $productId)->first()) {
            return $inventory;
        }

        if (!$this->currentHouseId()) {
            return null;
        }

        return Inventory::withoutGlobalScope('house')
            ->where('product_id', $productId)
            ->whereNull('house_id')
            ->first();
    }

    /**
     * Nạp sẵn vật tư theo danh sách mã bằng vài query, thay vì mỗi dòng một query.
     * Trả về map: mã (chữ hoa) => Product.
     */
    private function preloadProductsByCode(array $codes): array
    {
        $codes = array_values(array_unique(array_filter($codes)));
        if (empty($codes)) {
            return [];
        }

        $map = [];
        foreach (array_chunk($codes, 500) as $chunk) {
            foreach (Product::whereIn('code', $chunk)->get() as $product) {
                $map[strtoupper($product->code)] = $product;
            }
        }

        $missing = array_values(array_diff($codes, array_keys($map)));

        if ($this->currentHouseId() && $missing) {
            foreach (array_chunk($missing, 500) as $chunk) {
                $legacy = Product::withoutGlobalScope('house')
                    ->whereIn('code', $chunk)
                    ->whereNull('house_id')
                    ->get();

                foreach ($legacy as $product) {
                    $map[strtoupper($product->code)] = $product;
                }
            }
        }

        return $map;
    }

    /**
     * Nạp sẵn tồn kho theo danh sách product_id. Trả về map: product_id => Inventory.
     */
    private function preloadInventories(array $productIds): array
    {
        $productIds = array_values(array_unique(array_filter($productIds)));
        if (empty($productIds)) {
            return [];
        }

        $map = [];
        foreach (array_chunk($productIds, 500) as $chunk) {
            foreach (Inventory::whereIn('product_id', $chunk)->get() as $inventory) {
                $map[$inventory->product_id] = $inventory;
            }
        }

        $missing = array_values(array_diff($productIds, array_keys($map)));

        if ($this->currentHouseId() && $missing) {
            foreach (array_chunk($missing, 500) as $chunk) {
                $legacy = Inventory::withoutGlobalScope('house')
                    ->whereIn('product_id', $chunk)
                    ->whereNull('house_id')
                    ->get();

                foreach ($legacy as $inventory) {
                    $map[$inventory->product_id] = $inventory;
                }
            }
        }

        return $map;
    }
}
