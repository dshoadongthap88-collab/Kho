<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\InventoryTransaction;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    /**
     * Nhập kho sản phẩm
     */
    public function import(int $productId, float $quantity, string $referenceType = null, int $referenceId = null, string $note = null, string $batchNumber = null, $expiryDate = null, string $location = null)
    {
        return DB::transaction(function () use ($productId, $quantity, $referenceType, $referenceId, $note, $batchNumber, $expiryDate, $location) {
            $inventory = Inventory::firstOrCreate(['product_id' => $productId]);
            $inventory->increment('quantity', $quantity);
            
            // Nếu có vị trí mới, cập nhật luôn vị trí chính trong bảng tồn kho
            if ($location) {
                $inventory->warehouse_location = $location;
                $inventory->save();
            }

            return InventoryTransaction::create([
                'product_id' => $productId,
                'type' => 'import',
                'quantity' => $quantity,
                'batch_number' => $batchNumber,
                'expiry_date' => $expiryDate,
                'warehouse_location' => $location,
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
    public function export(int $productId, float $quantity, string $referenceType = null, int $referenceId = null, string $note = null, string $batchNumber = null, $expiryDate = null, string $location = null)
    {
        return DB::transaction(function () use ($productId, $quantity, $referenceType, $referenceId, $note, $batchNumber, $expiryDate, $location) {
            $inventory = Inventory::where('product_id', $productId)->firstOrFail();

            if ($inventory->quantity < $quantity) {
                throw new \Exception("Không đủ hàng trong kho để xuất.");
            }

            $inventory->decrement('quantity', $quantity);

            return InventoryTransaction::create([
                'product_id' => $productId,
                'type' => 'export',
                'quantity' => -$quantity,
                'batch_number' => $batchNumber,
                'expiry_date' => $expiryDate,
                'warehouse_location' => $location,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'note' => $note,
                'created_by' => auth()->id(),
            ]);
        });
    }

    /**
     * Điều chỉnh tồn kho
     */
    public function adjustQuantity(int $productId, int $newQuantity, string $note = null)
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

    /**
     * Lấy danh sách các lô hàng có sẵn của một sản phẩm
     */
    public function getAvailableBatches(int $productId)
    {
        return InventoryTransaction::where('product_id', $productId)
            ->select('batch_number', 'expiry_date', 'warehouse_location', DB::raw('SUM(quantity) as stock'))
            ->groupBy('batch_number', 'expiry_date', 'warehouse_location')
            ->having('stock', '>', 0)
            ->get();
    }
}
