<?php

namespace App\Livewire\Warehouse;

use App\Models\Product;
use App\Models\Supplier;
use App\Services\InventoryService;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

class StockInForm extends Component
{
    public $items = [];
    public $supplier_name = '';
    public $manufacturer = '';
    public $note = '';
    public $type = 'manual';

    // Modal tạo nhanh sản phẩm
    public $showProductModal = false;
    public $newPCode = '';
    public $newPName = '';
    public $newPUnit = 'Cái';

    protected $rules = [
        'items.*.product_id' => 'required|exists:products,id',
        'items.*.quantity' => 'required|integer|min:1',
    ];

    public function mount()
    {
        if (empty($this->items)) {
            $this->addItem();
        }
    }

    public function canAddItem()
    {
        if (empty($this->items)) {
            return true;
        }

        $lastItem = end($this->items);

        return !empty($lastItem['product_id']) && 
               !empty($lastItem['batch_number']) && 
               !empty($lastItem['quantity']) && 
               $lastItem['quantity'] > 0;
    }

    public function addItem()
    {
        if (!$this->canAddItem()) {
            return;
        }

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

    public function openProductModal()
    {
        $this->newPCode = 'P' . str_pad(Product::count() + 1, 4, '0', STR_PAD_LEFT);
        $this->newPName = '';
        $this->newPUnit = 'Cái';
        $this->showProductModal = true;
    }

    public function createProduct()
    {
        $this->validate([
            'newPCode' => 'required|unique:products,code',
            'newPName' => 'required|string',
            'newPUnit' => 'required|string',
        ]);

        $product = Product::create([
            'code' => $this->newPCode,
            'name' => $this->newPName,
            'unit' => $this->newPUnit,
            'brand' => $this->manufacturer, // Đồng bộ hãng từ header
            'status' => 'active',
        ]);

        $this->showProductModal = false;
        
        // Tự động thêm dòng mới với sản phẩm vừa tạo
        $this->addItemWithProduct($product->id);
        
        session()->flash('modal_success', 'Đã tạo sản phẩm mới và thêm vào phiếu!');
    }

    public function addItemWithProduct($productId)
    {
        // Chèn vào dòng trống cuối cùng nếu có, hoặc thêm dòng mới
        $lastIndex = count($this->items) - 1;
        if ($lastIndex >= 0 && empty($this->items[$lastIndex]['product_id'])) {
            $this->items[$lastIndex]['product_id'] = $productId;
            $this->updatedItems($productId, "items.{$lastIndex}.product_id");
        } else {
            $this->items[] = [
                'product_id' => $productId,
                'batch_number' => '',
                'expiry_date' => '',
                'warehouse_location' => '',
                'quantity' => 1
            ];
            $index = count($this->items) - 1;
            $this->updatedItems($productId, "items.{$index}.product_id");
        }
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
                'manufacturer' => $this->manufacturer,
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
            $this->reset(['items', 'supplier_name', 'manufacturer', 'note']);
            $this->addItem();
        });
    }

    public function render()
    {
        return view('livewire.warehouse.stock-in-form', [
            'products' => Product::where('status', 'active')->orderBy('name')->get(),
            'suppliers' => Supplier::orderBy('name')->get(),
            'brands' => Product::whereNotNull('brand')->distinct()->pluck('brand'),
        ]);
    }
}
