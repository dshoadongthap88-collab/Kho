<?php

namespace App\Livewire\Warehouse;

use App\Models\Inventory;
use App\Models\Product;
use App\Services\InventoryService;
use Livewire\Component;

class StockCountForm extends Component
{
    public $type = 'daily'; // 'daily' hoặc 'monthly'
    public $search = '';
    
    public $countItems = [];
    public $selectedItems = []; // Array of indices or product IDs
    public $note = '';

    public function mount()
    {
        $this->loadItems();
    }

    public function setType($type)
    {
        $this->type = $type;
        $this->search = '';
        $this->loadItems();
    }

    public function updatedSearch()
    {
        if ($this->type === 'monthly') {
            $this->loadItems();
        }
    }

    public function loadItems()
    {
        $this->countItems = [];
        $this->selectedItems = [];

        $query = Inventory::with('product');

        if ($this->type === 'daily') {
            // Lấy 1 vị trí ngẫu nhiên có hàng
            $randomLocation = Inventory::whereNotNull('warehouse_location')
                                       ->where('warehouse_location', '!=', '')
                                       ->inRandomOrder()
                                       ->value('warehouse_location');
                                       
            if ($randomLocation) {
                // Ưu tiên theo vị trí đó trước
                $query->orderByRaw("CASE WHEN warehouse_location = ? THEN 0 ELSE 1 END", [$randomLocation])
                      ->orderBy('warehouse_location');
            } else {
                $query->orderBy('warehouse_location');
            }
            // Lấy 10 mã
            $inventories = $query->limit(10)->get();
            
        } else {
            // Monthly: Toàn bộ, có lọc
            if ($this->search) {
                $query->whereHas('product', function($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('code', 'like', '%' . $this->search . '%');
                });
            }
            $inventories = $query->get();
        }

        foreach ($inventories as $inv) {
            $this->countItems[] = [
                'id' => $inv->id,
                'product_id' => $inv->product_id,
                'product_name' => $inv->product->name,
                'product_code' => $inv->product->code,
                'batch_number' => $inv->product->batch_number,
                'expiry_date' => $inv->product->expiry_date,
                'location' => $inv->warehouse_location,
                'unit' => $inv->product->unit,
                'system_quantity' => $inv->quantity,
                'actual_quantity' => $inv->quantity,
                'difference' => 0,
            ];
        }
    }

    public function toggleSelectAll($allIndices)
    {
        if (count($this->selectedItems) === count($allIndices)) {
            $this->selectedItems = [];
        } else {
            $this->selectedItems = $allIndices;
        }
    }

    public function updateDifference($index)
    {
        $item = $this->countItems[$index];
        $this->countItems[$index]['difference'] = $item['actual_quantity'] - $item['system_quantity'];
    }

    public function save()
    {
        $service = app(InventoryService::class);

        $prefix = $this->type === 'daily' ? 'SCD-' : 'SCM-';
        $stockCount = \App\Models\StockCount::create([
            'code' => $prefix . date('Ymd') . '-' . str_pad(\App\Models\StockCount::count() + 1, 4, '0', STR_PAD_LEFT),
            'status' => 'completed',
            'note' => $this->note,
            'created_by' => auth()->id(),
        ]);

        foreach ($this->countItems as $item) {
            if ($item['difference'] != 0) {
                $service->adjustQuantity(
                    $item['product_id'],
                    $item['actual_quantity'],
                    "Kiểm kê #{$stockCount->code}: Chênh lệch {$item['difference']}"
                );
            }
        }

        session()->flash('success', 'Kiểm kê hoàn tất!');
        return redirect()->route('warehouse.inventory');
    }

    public function render()
    {
        return view('livewire.warehouse.stock-count-form');
    }
}
