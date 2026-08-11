<?php

namespace App\Livewire\Warehouse;

use Livewire\Component;
use App\Models\Asset;
use App\Models\MaintenanceBom;
use App\Models\MaintenanceBomItem;
use App\Models\Product;
use Illuminate\Support\Str;

class MaintenanceBomManager extends Component
{
    public $selectedAssetId = null;
    public $selectedBomId = null;
    public $searchAsset = '';

    // BOM Modal (Maintenance Level)
    public $showBomModal = false;
    public $isEditBom = false;
    public $editBomId = null;
    public $maintenance_level = '';
    public $cycle = 0;

    // Bulk Add Modal
    public $showProductPickerModal = false;
    public $searchProduct = '';
    public $selectedProductIds = [];
    public $selectAllProducts = false;

    // Inline Editing
    public $bomItemQuantities = [];
    public $bomItemNotes = [];

    // Copy Modal
    public $showCopyModal = false;
    public $copyFromBomId = null;

    public function selectAsset($id)
    {
        $this->selectedAssetId = $id;
        $this->selectedBomId = null;
    }

    public function selectBom($id)
    {
        $this->selectedBomId = $id;
        $this->loadBomItemsData();
    }

    public function loadBomItemsData()
    {
        $this->bomItemQuantities = [];
        $this->bomItemNotes = [];
        if ($this->selectedBomId) {
            $items = MaintenanceBomItem::where('maintenance_bom_id', $this->selectedBomId)->get();
            foreach ($items as $item) {
                $this->bomItemQuantities[$item->id] = (float)$item->quantity;
                $this->bomItemNotes[$item->id] = $item->note;
            }
        }
    }

    // --- CRUD Maintenance Level (BOM) ---

    public function openBomModal($id = null)
    {
        $this->reset(['maintenance_level', 'cycle', 'editBomId']);
        $this->isEditBom = false;
        
        if ($id) {
            $bom = MaintenanceBom::find($id);
            if ($bom) {
                $this->isEditBom = true;
                $this->editBomId = $bom->id;
                $this->maintenance_level = $bom->maintenance_level;
                $this->cycle = $bom->cycle;
            }
        }
        $this->showBomModal = true;
    }

    public function saveBom()
    {
        // Loại bỏ khoảng trắng thừa (data rác)
        $this->maintenance_level = preg_replace('/\s+/', ' ', trim($this->maintenance_level ?? ''));
        if ($this->cycle === '' || $this->cycle === null) {
            $this->cycle = 0;
        }

        $this->validate([
            'maintenance_level' => 'required|string|max:255',
            'cycle' => 'required|numeric|min:0',
        ]);

        if ($this->isEditBom && $this->editBomId) {
            $bom = MaintenanceBom::find($this->editBomId);
            $bom->update([
                'maintenance_level' => $this->maintenance_level,
                'cycle' => $this->cycle,
            ]);
            session()->flash('message', 'Đã cập nhật Mức bảo dưỡng.');
        } else {
            MaintenanceBom::create([
                'asset_id' => $this->selectedAssetId,
                'bom_code' => 'BOM-' . strtoupper(Str::random(6)),
                'maintenance_level' => $this->maintenance_level,
                'cycle' => $this->cycle,
                'created_by' => auth()->id(),
            ]);
            session()->flash('message', 'Đã thêm Mức bảo dưỡng mới.');
        }

        $this->showBomModal = false;
    }

    public function deleteBom($id)
    {
        $bom = MaintenanceBom::find($id);
        if ($bom) {
            $bom->delete();
            if ($this->selectedBomId == $id) {
                $this->selectedBomId = null;
            }
            session()->flash('message', 'Đã xóa Mức bảo dưỡng.');
        }
    }

    // --- CRUD Items ---

    public function openProductPicker()
    {
        $this->reset(['searchProduct', 'selectedProductIds', 'selectAllProducts']);
        $this->showProductPickerModal = true;
    }

    public function toggleSelectAllProducts($productIds)
    {
        if ($this->selectAllProducts) {
            $this->selectedProductIds = $productIds;
        } else {
            $this->selectedProductIds = [];
        }
    }

    public function addSelectedProductsToBom()
    {
        if (empty($this->selectedProductIds)) {
            session()->flash('error', 'Vui lòng chọn ít nhất một vật tư.');
            return;
        }

        foreach ($this->selectedProductIds as $pid) {
            $exists = MaintenanceBomItem::where('maintenance_bom_id', $this->selectedBomId)
                ->where('product_id', $pid)
                ->exists();
                
            if (!$exists) {
                MaintenanceBomItem::create([
                    'maintenance_bom_id' => $this->selectedBomId,
                    'product_id' => $pid,
                    'quantity' => 1,
                    'note' => '',
                ]);
            }
        }

        $this->loadBomItemsData();
        $this->showProductPickerModal = false;
        session()->flash('message', 'Đã thêm vật tư vào BOM.');
    }

    public function saveBomItemsQuantities()
    {
        if (empty($this->bomItemQuantities)) return;

        foreach ($this->bomItemQuantities as $itemId => $qty) {
            $item = MaintenanceBomItem::find($itemId);
            if ($item) {
                // Xử lý data rác cho số lượng định mức
                $validQty = (is_numeric($qty) && $qty >= 0) ? (float)$qty : 0;
                $note = trim($this->bomItemNotes[$itemId] ?? '');
                
                $item->update([
                    'quantity' => $validQty,
                    'note' => $note,
                ]);
            }
        }
        
        $this->dispatch('bom-saved-success');
    }

    public function deleteItem($id)
    {
        $item = MaintenanceBomItem::find($id);
        if ($item) {
            $item->delete();
            unset($this->bomItemQuantities[$id]);
            unset($this->bomItemNotes[$id]);
            session()->flash('message', 'Đã xóa vật tư khỏi BOM.');
        }
    }

    // --- Copy BOM ---

    public function openCopyModal()
    {
        $this->reset(['copyFromBomId']);
        $this->showCopyModal = true;
    }

    public function copyBom()
    {
        $this->validate([
            'copyFromBomId' => 'required|exists:maintenance_boms,id',
        ]);

        if ($this->copyFromBomId == $this->selectedBomId) {
            session()->flash('error', 'Không thể sao chép từ chính BOM này.');
            return;
        }

        $sourceBom = MaintenanceBom::with('items')->find($this->copyFromBomId);
        if (!$sourceBom) return;

        foreach ($sourceBom->items as $item) {
            // only copy if not exists
            $exists = MaintenanceBomItem::where('maintenance_bom_id', $this->selectedBomId)
                ->where('product_id', $item->product_id)
                ->exists();
                
            if (!$exists) {
                MaintenanceBomItem::create([
                    'maintenance_bom_id' => $this->selectedBomId,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'note' => $item->note,
                ]);
            }
        }

        $this->loadBomItemsData();
        session()->flash('message', 'Đã sao chép vật tư thành công.');
        $this->showCopyModal = false;
    }

    public function render()
    {
        // 1. Assets
        $assets = Asset::when($this->searchAsset, function($query) {
            $query->where('name', 'like', '%' . $this->searchAsset . '%')
                  ->orWhere('equipment_code', 'like', '%' . $this->searchAsset . '%')
                  ->orWhere('asset_code', 'like', '%' . $this->searchAsset . '%');
        })->orderBy('name')->get();

        // 2. BOMs for selected asset
        $boms = [];
        if ($this->selectedAssetId) {
            $boms = MaintenanceBom::where('asset_id', $this->selectedAssetId)
                ->orderBy('cycle', 'asc')
                ->get();
        }

        // 3. Items for selected BOM
        $bomItems = [];
        if ($this->selectedBomId) {
            $bomItems = MaintenanceBomItem::with(['product.inventory'])
                ->where('maintenance_bom_id', $this->selectedBomId)
                ->get();
        }

        // 4. Search Products for Picker Modal
        $products = collect();
        $allProductIdsOnPage = [];
        if ($this->showProductPickerModal) {
            $products = Product::where(function($q) {
                    $q->where('name', 'like', '%' . $this->searchProduct . '%')
                      ->orWhere('code', 'like', '%' . $this->searchProduct . '%');
                })
                ->where(function($q) {
                    $q->where('type', '!=', 'material')
                      ->orWhereNull('type');
                })
                ->orderBy('name')
                ->take(50)
                ->get();
            $allProductIdsOnPage = $products->pluck('id')->toArray();
        }

        // 5. Other BOMs for copy
        $otherBoms = [];
        if ($this->showCopyModal && $this->selectedAssetId) {
            $otherBoms = MaintenanceBom::where('asset_id', $this->selectedAssetId)
                ->where('id', '!=', $this->selectedBomId)
                ->orderBy('cycle', 'asc')
                ->get();
        }

        return view('livewire.warehouse.maintenance-bom-manager', compact(
            'assets',
            'boms',
            'bomItems',
            'products',
            'allProductIdsOnPage',
            'otherBoms'
        ));
    }
}
