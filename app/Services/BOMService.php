<?php

namespace App\Services;

use App\Models\Bom;
use App\Models\Product;
use Illuminate\Support\Collection;

class BOMService
{
    /**
     * Tính toán lượng nguyên vật liệu cần thiết cho một số lượng sản phẩm nhất định
     * 
     * @param int $productId ID của sản phẩm thành phẩm
     * @param float $quantity Số lượng sản phẩm cần sản xuất
     * @return Collection
     */
    public function calculateMaterials(int $productId, float $quantity): Collection
    {
        $boms = Bom::where('product_id', $productId)->with('material')->get();

        return $boms->map(function ($bom) use ($quantity) {
            return [
                'material_id' => $bom->material_id,
                'material_name' => $bom->material->name,
                'required_quantity' => $bom->quantity * $quantity,
                'unit' => $bom->unit ?? $bom->material->unit,
            ];
        });
    }

    /**
     * Kiểm tra xem kho có đủ nguyên vật liệu để sản xuất hay không
     */
    public function checkMaterialAvailability(int $productId, float $quantity): array
    {
        $requiredMaterials = $this->calculateMaterials($productId, $quantity);
        $availability = [];
        $canProduce = true;

        foreach ($requiredMaterials as $material) {
            $inventory = \App\Models\Inventory::where('product_id', $material['material_id'])->first();
            $availableQty = $inventory ? ($inventory->quantity - $inventory->reserved_quantity) : 0;
            
            $isSufficient = $availableQty >= $material['required_quantity'];
            if (!$isSufficient) {
                $canProduce = false;
            }

            $availability[] = [
                'material_id' => $material['material_id'],
                'material_name' => $material['material_name'],
                'required' => $material['required_quantity'],
                'available' => $availableQty,
                'is_sufficient' => $isSufficient,
            ];
        }

        return [
            'can_produce' => $canProduce,
            'details' => $availability,
        ];
    }
}
