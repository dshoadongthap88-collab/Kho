<?php

namespace App\Livewire\Warehouse;

use App\Models\Inventory;
use App\Models\Product;
use App\Services\InventoryService;
use Livewire\Component;

class StockCountForm extends Component
{
    public $countItems = [];
    public $note = '';

    public function mount()
    {
        $inventories = Inventory::with('product')->get();
        foreach ($inventories as $inv) {
            $this->countItems[] = [
                'product_id' => $inv->product_id,
                'product_name' => $inv->product->name,
                'product_code' => $inv->product->code,
                'system_quantity' => $inv->quantity,
                'actual_quantity' => $inv->quantity,
                'difference' => 0,
            ];
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

        $stockCount = \App\Models\StockCount::create([
            'code' => 'SC-' . date('Ymd') . '-' . str_pad(\App\Models\StockCount::count() + 1, 4, '0', STR_PAD_LEFT),
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
