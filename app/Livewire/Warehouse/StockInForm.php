<?php

namespace App\Livewire\Warehouse;

use App\Models\Product;
use App\Services\InventoryService;
use Livewire\Component;

class StockInForm extends Component
{
    public $items = [];
    public $supplier_name = '';
    public $note = '';
    public $type = 'manual';

    protected $rules = [
        'items.*.product_id' => 'required|exists:products,id',
        'items.*.quantity' => 'required|integer|min:1',
    ];

    public function mount()
    {
        $this->addItem();
    }

    public function addItem()
    {
        $this->items[] = ['product_id' => '', 'quantity' => 1];
    }

    public function removeItem($index)
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function save()
    {
        $this->validate();

        $service = app(InventoryService::class);

        $stockIn = \App\Models\StockIn::create([
            'code' => 'SI-' . date('Ymd') . '-' . str_pad(\App\Models\StockIn::count() + 1, 4, '0', STR_PAD_LEFT),
            'supplier_name' => $this->supplier_name,
            'type' => $this->type,
            'status' => 'completed',
            'note' => $this->note,
            'created_by' => auth()->id(),
        ]);

        foreach ($this->items as $item) {
            $service->import(
                $item['product_id'],
                $item['quantity'],
                'stock_in',
                $stockIn->id,
                $this->note
            );
        }

        session()->flash('success', 'Nhập kho thành công!');
        $this->reset(['items', 'supplier_name', 'note']);
        $this->addItem();
    }

    public function render()
    {
        return view('livewire.warehouse.stock-in-form', [
            'products' => Product::where('status', 'active')->orderBy('name')->get(),
        ]);
    }
}
