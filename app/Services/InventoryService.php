<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\InventoryTransaction;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    /**
     * Nhập kho sản phẩm
     */
    public function import(int $productId, int $quantity, string $referenceType = null, int $referenceId = null, string $note = null)
    {
        return DB::transaction(function () use ($productId, $quantity, $referenceType, $referenceId, $note) {
            $inventory = Inventory::firstOrCreate(
                ['product_id' => $productId],
                ['quantity' => 0, 'reserved_quantity' => 0]
            );

            $inventory->increment('quantity', $quantity);

            return InventoryTransaction::create([
                'product_id' => $productId,
                'type' => 'import',
                'quantity' => $quantity,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'note' => $note,
                'created_by' => auth()->id(),
            ]);
        });
    }

    /**
     * Xuất kho sản phẩm
     */
    public function export(int $productId, int $quantity, string $referenceType = null, int $referenceId = null, string $note = null)
    {
        return DB::transaction(function () use ($productId, $quantity, $referenceType, $referenceId, $note) {
            $inventory = Inventory::where('product_id', $productId)->firstOrFail();

            if ($inventory->quantity < $quantity) {
                throw new \Exception("Không đủ hàng trong kho để xuất.");
            }

            $inventory->decrement('quantity', $quantity);

            return InventoryTransaction::create([
                'product_id' => $productId,
                'type' => 'export',
                'quantity' => -$quantity,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'note' => $note,
                'created_by' => auth()->id(),
            ]);
        });
    }

    /**
     * Giữ hàng (Reserve) cho đơn hàng
     */
    public function reserve(int $productId, int $quantity, string $referenceType = null, int $referenceId = null)
    {
        return DB::transaction(function () use ($productId, $quantity, $referenceType, $referenceId) {
            $inventory = Inventory::where('product_id', $productId)->firstOrFail();

            $available = $inventory->quantity - $inventory->reserved_quantity;
            if ($available < $quantity) {
                throw new \Exception("Không đủ hàng khả dụng để giữ chỗ.");
            }

            $inventory->increment('reserved_quantity', $quantity);

            return InventoryTransaction::create([
                'product_id' => $productId,
                'type' => 'reserve',
                'quantity' => $quantity,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'created_by' => auth()->id(),
            ]);
        });
    }

    /**
     * Giải phóng hàng giữ chỗ (Release)
     */
    public function release(int $productId, int $quantity, string $referenceType = null, int $referenceId = null)
    {
        return DB::transaction(function () use ($productId, $quantity, $referenceType, $referenceId) {
            $inventory = Inventory::where('product_id', $productId)->firstOrFail();

            $inventory->decrement('reserved_quantity', $quantity);

            return InventoryTransaction::create([
                'product_id' => $productId,
                'type' => 'release',
                'quantity' => -$quantity,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'created_by' => auth()->id(),
            ]);
        });
    }

    /**
     * Điều chỉnh kho (Adjust) sau kiểm kê
     */
    public function adjust(int $productId, int $newQuantity, string $note = null)
    {
        return DB::transaction(function () use ($productId, $newQuantity, $note) {
            $inventory = Inventory::where('product_id', $productId)->firstOrFail();
            
            $difference = $newQuantity - $inventory->quantity;
            $inventory->quantity = $newQuantity;
            $inventory->save();

            return InventoryTransaction::create([
                'product_id' => $productId,
                'type' => 'adjust',
                'quantity' => $difference,
                'note' => $note,
                'created_by' => auth()->id(),
            ]);
        });
    }
}
