<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\InventoryTransaction;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    /**
     * Khoá và lấy bản ghi tồn kho của vật tư TRONG dự án đang đứng.
     * Bản ghi cũ chưa gắn house_id vẫn được nhận, nhưng xếp sau bản ghi đúng dự án.
     */
    private function lockInventoryForCurrentHouse(int $productId): ?Inventory
    {
        $houseId = session('current_house') ?? (auth()->user()?->current_house_id);

        return Inventory::withoutGlobalScopes()
            ->where('product_id', $productId)
            ->when($houseId, function ($query) use ($houseId) {
                $query->where(function ($sub) use ($houseId) {
                    $sub->where('house_id', $houseId)->orWhereNull('house_id');
                });
            })
            ->orderByRaw('house_id IS NULL')
            ->lockForUpdate()
            ->first();
    }

    /**
     * Nhập kho sản phẩm
     */
    public function import(int $productId, float $quantity, string $referenceType = null, int $referenceId = null, string $note = null, string $batchNumber = null, $expiryDate = null, string $location = null)
    {
        return DB::transaction(function () use ($productId, $quantity, $referenceType, $referenceId, $note, $batchNumber, $expiryDate, $location) {
            // Chỉ lấy tồn kho của dự án đang đứng. Bản ghi cũ có house_id = null vẫn
            // được chấp nhận (tránh lỗi 404), nhưng luôn ưu tiên bản ghi đúng dự án.
            $inventory = $this->lockInventoryForCurrentHouse($productId);

            if (!$inventory) {
                try {
                    $inventory = Inventory::create(['product_id' => $productId]);
                } catch (\Exception $e) {
                    $inventory = $this->lockInventoryForCurrentHouse($productId);
                    if (!$inventory) {
                        throw $e;
                    }
                }
            }

            $inventory->quantity += $quantity;
            
            // Nếu có vị trí mới, cập nhật luôn vị trí chính trong bảng tồn kho
            if ($location) {
                $inventory->warehouse_location = $location;
            }
            
            // Cập nhật house_id nếu đang bị null
            if (empty($inventory->house_id)) {
                $houseId = session('current_house') ?? (auth()->user()?->current_house_id);
                if ($houseId) {
                    $inventory->house_id = $houseId;
                }
            }
            
            $inventory->save();

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
     * Nếu tồn = 0 hoặc không đủ, chỉ trừ số lượng có sẵn (không throw exception)
     */
    public function export(int $productId, float $quantity, string $referenceType = null, int $referenceId = null, string $note = null, string $batchNumber = null, $expiryDate = null, string $location = null)
    {
        return DB::transaction(function () use ($productId, $quantity, $referenceType, $referenceId, $note, $batchNumber, $expiryDate, $location) {
            $inventory = $this->lockInventoryForCurrentHouse($productId);

            if (!$inventory) {
                throw new \Exception("Không tìm thấy tồn kho của vật tư này trong chi nhánh hiện tại.");
            }

            // Tính số lượng thực tế có thể trừ (chỉ trừ nếu tồn > 0)
            $actualQuantityToDeduct = max(0, min($quantity, $inventory->quantity));

            // Chỉ trừ kho nếu có số lượng có sẵn
            if ($actualQuantityToDeduct > 0) {
                $inventory->decrement('quantity', $actualQuantityToDeduct);
            }

            return InventoryTransaction::create([
                'product_id' => $productId,
                'type' => 'export',
                'quantity' => -$actualQuantityToDeduct,
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
    public function adjustQuantity(int $productId, float $newQuantity, string $note = null)
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
            ->orderBy('expiry_date', 'asc')
            ->get();
    }
}
