<?php

namespace App\Livewire\Warehouse;

use App\Models\Bom;
use App\Models\Product;
use App\Services\BOMService;
use Livewire\Component;

class BomManager extends Component
{
    public $selectedProductId = '';
    public $bomItems = [];
    public $newMaterialId = '';
    public $newQuantity = 1;
    public $newUnit = '';

    public function updatedSelectedProductId($value)
    {
        if ($value) {
            $this->loadBom($value);
        } else {
            $this->bomItems = [];
        }
    }

    public function loadBom($productId)
    {
        $boms = Bom::where('product_id', $productId)->with('material')->get();
        $this->bomItems = $boms->map(fn($b) => [
            'id' => $b->id,
            'material_id' => $b->material_id,
            'material_name' => $b->material->name,
            'quantity' => $b->quantity,
            'unit' => $b->unit,
        ])->toArray();
    }

    public function addMaterial()
    {
        $this->validate([
            'selectedProductId' => 'required',
            'newMaterialId' => 'required|exists:products,id',
            'newQuantity' => 'required|numeric|min:0.001',
        ]);

        $material = Product::find($this->newMaterialId);

        Bom::updateOrCreate(
            ['product_id' => $this->selectedProductId, 'material_id' => $this->newMaterialId],
            ['quantity' => $this->newQuantity, 'unit' => $this->newUnit ?: $material->unit]
        );

        $this->loadBom($this->selectedProductId);
        $this->reset(['newMaterialId', 'newQuantity', 'newUnit']);
        session()->flash('success', 'Đã thêm/cập nhật NVL!');
    }

    public function removeMaterial($bomId)
    {
        Bom::destroy($bomId);
        $this->loadBom($this->selectedProductId);
        session()->flash('success', 'Đã xóa NVL!');
    }

    public function saveBom()
    {
        // BOM items are already saved individually via add/remove. 
        // This button acts as a confirmation for the user context.
        session()->flash('success', 'Đã lưu cấu hình định mức BOM!');
    }

    public function render()
    {
        // Thành phẩm: Các mã không bắt đầu bằng NVL (có thể là SP, TP...)
        $products = Product::where('code', 'not like', 'NVL%')
            ->where('status', 'active')
            ->orderBy('name')
            ->get();
            
        // Nguyên vật liệu: Các mã bắt đầu bằng NVL
        $materials = Product::where('code', 'like', 'NVL%')
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $availability = null;
        if ($this->selectedProductId && count($this->bomItems) > 0) {
            $bomService = app(BOMService::class);
            $availability = $bomService->checkMaterialAvailability($this->selectedProductId, 1);
        }

        return view('livewire.warehouse.bom-manager', [
            'products' => $products,
            'materials' => $materials,
            'availability' => $availability,
        ]);
    }
}
