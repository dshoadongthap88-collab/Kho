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
 * Bản ghi cũ chưa gắn house_id (house_id = null) vẫn được tái sử dụng, nhưng sẽ
 * được gán về dự án hiện tại thay vì dùng chung ngầm giữa các dự án.
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

        $houseId = $this->currentHouseId();
        if (!$houseId) {
            return null;
        }

        $legacy = Product::withoutGlobalScope('house')
            ->where('code', $code)
            ->whereNull('house_id')
            ->first();

        if ($legacy) {
            $legacy->house_id = $houseId;
            $legacy->save();
        }

        return $legacy;
    }

    private function findInventoryForCurrentHouse(int $productId): ?Inventory
    {
        if ($inventory = Inventory::where('product_id', $productId)->first()) {
            return $inventory;
        }

        $houseId = $this->currentHouseId();
        if (!$houseId) {
            return null;
        }

        $legacy = Inventory::withoutGlobalScope('house')
            ->where('product_id', $productId)
            ->whereNull('house_id')
            ->first();

        if ($legacy) {
            $legacy->house_id = $houseId;
            $legacy->save();
        }

        return $legacy;
    }
}
