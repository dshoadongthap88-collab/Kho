<?php

namespace App\Livewire\Warehouse;

use App\Models\Product;
use App\Services\InventoryService;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

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
        $this->items[] = [
            'product_id' => '',
            'batch_number' => '',
            'expiry_date' => '',
            'warehouse_location' => '',
            'quantity' => 1
        ];
    }

    public function updatedItems($value, $key)
    {
        // Khi chọn sản phẩm, tự động gợi ý vị trí kho cũ
        if (str_ends_with($key, '.product_id')) {
            $index = explode('.', $key)[1];
            $productId = $value;
            
            if ($productId) {
                $product = Product::find($productId);
                if ($product && $product->location) {
                    $this->items[$index]['warehouse_location'] = $product->location;
                }
            }
        }
    }

    public function removeItem($index)
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function save()
    {
        $this->validate([
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.batch_number' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:0.0001',
            'supplier_name' => 'nullable|string',
        ]);

        $service = app(InventoryService::class);

        return DB::transaction(function () use ($service) {
            $stockIn = \App\Models\StockIn::create([
                'code' => 'SI-' . date('Ymd') . '-' . str_pad(\App\Models\StockIn::count() + 1, 4, '0', STR_PAD_LEFT),
                'supplier_name' => $this->supplier_name,
                'type' => $this->type,
                'status' => 'completed',
                'note' => $this->note,
                'created_by' => auth()->id(),
            ]);

            foreach ($this->items as $item) {
                // Tạo StockInItem
                \App\Models\StockInItem::create([
                    'stock_in_id' => $stockIn->id,
                    'product_id' => $item['product_id'],
                    'batch_number' => $item['batch_number'],
                    'expiry_date' => $item['expiry_date'] ?: null,
                    'warehouse_location' => $item['warehouse_location'],
                    'quantity' => $item['quantity'],
                ]);

                // Gọi Service để cập nhật tồn kho và tạo giao dịch
                $service->import(
                    $item['product_id'],
                    $item['quantity'],
                    'stock_in',
                    $stockIn->id,
                    $this->note,
                    $item['batch_number'],
                    $item['expiry_date'] ?: null
                );
                
                // Cập nhật vị trí mặc định của sản phẩm nếu có
                if ($item['warehouse_location']) {
                    Product::where('id', $item['product_id'])->update(['location' => $item['warehouse_location']]);
                }
            }

            session()->flash('success', 'Nhập kho thành công!');
            $this->reset(['items', 'supplier_name', 'note']);
            $this->addItem();
        });
    }

    public function render()
    {
        return view('livewire.warehouse.stock-in-form', [
            'products' => Product::where('status', 'active')->orderBy('name')->get(),
        ]);
    }
}
