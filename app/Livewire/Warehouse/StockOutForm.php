<?php

namespace App\Livewire\Warehouse;

use App\Models\Product;
use App\Services\InventoryService;
use Livewire\Component;

class StockOutForm extends Component
{
    public $items = [];
    public $customer_name = '';
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

        $stockOut = \App\Models\StockOut::create([
            'code' => 'SO-' . date('Ymd') . '-' . str_pad(\App\Models\StockOut::count() + 1, 4, '0', STR_PAD_LEFT),
            'customer_name' => $this->customer_name,
            'type' => $this->type,
            'status' => 'completed',
            'note' => $this->note,
            'created_by' => auth()->id(),
        ]);

        foreach ($this->items as $item) {
            $service->export(
                $item['product_id'],
                $item['quantity'],
                'stock_out',
                $stockOut->id,
                $this->note
            );
        }

        session()->flash('success', 'Xuất kho thành công!');
        $this->reset(['items', 'customer_name', 'note']);
        $this->addItem();
    }

    public function render()
    {
        return view('livewire.warehouse.stock-out-form', [
            'products' => Product::where('status', 'active')->orderBy('name')->get(),
        ]);
    }
}
