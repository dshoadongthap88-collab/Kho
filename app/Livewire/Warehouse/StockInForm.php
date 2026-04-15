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
    public $type = 'purchase_produced';

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
            'product_search' => '',
            'batch_number' => '',
            'expiry_date' => '',
            'warehouse_location' => '',
            'quantity' => 1,
            'unit_price' => 0,
            'vat_rate' => 0,
            'total_amount' => 0
        ];
    }

    public function updatedType($value)
    {
        // Khi người dùng thay đổi Loại nhập, reset lại các dòng trắng hoàn toàn
        $this->items = [];
        $this->addItem();
    }

    public function updated($name, $value)
    {
        // Khi người dùng chọn sản phẩm từ ô tìm kiếm (items.0.product_search)
        if (str_contains($name, 'items') && str_ends_with($name, '.product_search')) {
            $parts = explode('.', $name);
            $index = $parts[1];
            
            if (!$value) {
                $this->items[$index]['product_id'] = '';
                $this->items[$index]['warehouse_location'] = '';
                $this->items[$index]['batch_number'] = '';
                $this->items[$index]['expiry_date'] = '';
                return;
            }

            // Tìm sản phẩm (không phân biệt hoa thường)
            $product = null;
            $searchValue = trim($value);
            
            if (str_contains($searchValue, ' - ')) {
                $code = trim(explode(' - ', $searchValue)[0]);
                $product = Product::whereRaw('LOWER(code) = ?', [strtolower($code)])->first();
            }
            
            if (!$product) {
                $product = Product::whereRaw('LOWER(code) = ?', [strtolower($searchValue)])->first();
            }
            
            if (!$product) {
                $product = Product::whereRaw('LOWER(name) = ?', [strtolower($searchValue)])->first();
            }
            
            if (!$product) return;

            // === ĐÃ TÌM THẤY SẢN PHẨM ===
            $this->items[$index]['product_id'] = $product->id;
            
            // Tự động điền dữ liệu từ danh mục sản phẩm
            // Lấy UNIT thông minh: Thử Unit -> Box Spec -> Carton Spec
            $this->items[$index]['unit'] = $product->unit ?: ($product->box_spec ?: ($product->carton_spec ?: '-'));
            $this->items[$index]['warehouse_location'] = $product->location ?: '';
            $this->items[$index]['batch_number'] = $product->batch_number ?: '';
            $this->items[$index]['expiry_date'] = $product->expiry_date ? $product->expiry_date->format('Y-m-d') : '';
            $this->items[$index]['unit_price'] = $product->price ?? 0;
            $this->items[$index]['vat_rate'] = 0;
            $this->calculateTotal($index);
        }

        // Khi thay đổi giá hoặc số lượng thì tính lại thành tiền
        if (str_contains($name, 'items') && (str_ends_with($name, '.quantity') || str_ends_with($name, '.unit_price') || str_ends_with($name, '.vat_rate'))) {
            $parts = explode('.', $name);
            $index = $parts[1];
            $this->calculateTotal($index);
        }
    }

    public function calculateTotal($index)
    {
        $qty = floatval($this->items[$index]['quantity'] ?? 0);
        $price = floatval($this->items[$index]['unit_price'] ?? 0);
        $vat = floatval($this->items[$index]['vat_rate'] ?? 0);

        $subtotal = $qty * $price;
        $this->items[$index]['total_amount'] = $subtotal + ($subtotal * $vat / 100);
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

        $productType = 'product_purchased'; // default
        if ($this->type === 'import_material') {
            $productType = 'material';
        } elseif ($this->type === 'production') {
            $productType = 'product_produced';
        }

        $product = Product::create([
            'code' => $this->newPCode,
            'name' => $this->newPName,
            'unit' => $this->newPUnit,
            'brand' => $this->manufacturer, // Đồng bộ hãng từ header
            'status' => 'active',
            'type' => $productType,
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
            $product = Product::find($productId);
            if ($product) {
                $this->items[$lastIndex]['product_search'] = $product->code . ' - ' . $product->name;
                if ($product->location) {
                    $this->items[$lastIndex]['warehouse_location'] = $product->location;
                }
            }
        } else {
            $product = Product::find($productId);
            $this->items[] = [
                'product_id' => $productId,
                'product_search' => $product ? ($product->code . ' - ' . $product->name) : '',
                'batch_number' => '',
                'expiry_date' => '',
                'warehouse_location' => $product?->location ?: '',
                'quantity' => 1,
                'unit_price' => $product?->price ?: 0,
                'vat_rate' => 0,
                'total_amount' => $product?->price ?: 0
            ];
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
                    'unit_price' => $item['unit_price'] ?? 0,
                    'vat_rate' => $item['vat_rate'] ?? 0,
                    'total_amount' => $item['total_amount'] ?? 0,
                ]);

                // Gọi Service để thực hiện nhập kho và tạo giao dịch
                $service->import(
                    $item['product_id'],
                    $item['quantity'],
                    'stock_in',
                    $stockIn->id,
                    $this->note,
                    $item['batch_number'],
                    $item['expiry_date'] ?: null,
                    $item['warehouse_location']
                );
                
                // Cập nhật vị trí mặc định và phân loại của sản phẩm
                $productUpdates = [];
                if ($item['warehouse_location']) {
                    $productUpdates['location'] = $item['warehouse_location'];
                }
                if ($this->type === 'import_material') {
                    $productUpdates['type'] = 'material';
                }
                
                if (!empty($productUpdates)) {
                    Product::where('id', $item['product_id'])->update($productUpdates);
                }
            }

            session()->flash('success', 'Nhập kho thành công!');
            $this->reset(['items', 'supplier_name', 'manufacturer', 'note']);
            $this->addItem();
        });
    }

    public function render()
    {
        $productQuery = Product::where('status', 'active');

        if ($this->type === 'import_material') {
            $productQuery->where('type', 'material');
        } else {
            // Các loại nhập khác (thành phẩm, v.v...)
            $productQuery->where('type', '!=', 'material');
        }

        return view('livewire.warehouse.stock-in-form', [
            'products' => $productQuery->orderBy('name')->get(),
            'suppliers' => Supplier::orderBy('name')->get(),
            'brands' => Product::whereNotNull('brand')->distinct()->pluck('brand'),
        ]);
    }
}
