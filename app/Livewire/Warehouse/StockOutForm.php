<?php

namespace App\Livewire\Warehouse;

use App\Models\Product;
use App\Models\StockOut;
use App\Models\StockOutItem;
use App\Models\Supplier;
use App\Services\InventoryService;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

class StockOutForm extends Component
{
    public $items = [];
    public $customer_name = '';
    public $customer_details = [
        'address' => '',
        'phone' => '',
        'email' => '',
        'contact_person' => ''
    ];
    public $receiver_department = '';
    public $note = '';
    public $type = 'production';

    // Biến cho quy trình "Xuất cho sản xuất"
    public $production_product_id = '';
    public $production_quantity = 1;

    // Các biến cho tính năng chọn lô hàng (Batch Selection)
    public $showBatchModal = false;
    public $availableBatches = [];
    public $activeItemIndex = null;

    protected $rules = [
        'items.*.product_id' => 'required|exists:products,id',
        'items.*.quantity' => 'required|numeric|min:0.0001',
        'items.*.batch_number' => 'nullable|string',
    ];

    public function mount()
    {
        if (empty($this->items)) {
            if ($this->type !== 'production') {
                $this->addItem();
            }
        }
    }

    public function canAddItem()
    {
        if (empty($this->items)) {
            return true;
        }

        if ($this->type === 'production') {
            // Không cho phép thêm thủ công nếu đang ở mode production
            return false;
        }

        $lastItem = end($this->items);

        return !empty($lastItem['product_id']) && 
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
            'product_search' => '', // Trường hiển thị để tìm kiếm
            'unit' => '',
            'brand' => '',
            'batch_number' => '',
            'expiry_date' => '',
            'warehouse_location' => '',
            'quantity' => 1,
            'unit_price' => 0,
            'vat_rate' => 0,
            'total_amount' => 0,
            'is_printed' => true
        ];
    }

    public function updated($name, $value)
    {
        // Khi chọn khách hàng
        if ($name === 'customer_name') {
            $customer = Supplier::where('name', $value)->first();
            if ($customer) {
                $this->customer_details = [
                    'address' => $customer->address ?: '',
                    'phone' => $customer->phone ?: '',
                    'email' => $customer->email ?: '',
                    'contact_person' => $customer->contact_person ?: ''
                ];
            } else {
                $this->customer_details = [
                    'address' => '', 'phone' => '', 'email' => '', 'contact_person' => ''
                ];
            }
        }

        // Khi người dùng chọn sản phẩm từ ô tìm kiếm (items.0.product_search)
        if (str_contains($name, 'items') && str_ends_with($name, '.product_search')) {
            $parts = explode('.', $name);
            $index = $parts[1];
            
            if (!$value) {
                // Nếu xóa trắng thì reset các trường liên quan
                $this->items[$index]['product_id'] = '';
                $this->items[$index]['unit'] = '';
                $this->items[$index]['brand'] = '';
                $this->items[$index]['batch_number'] = '';
                $this->items[$index]['expiry_date'] = '';
                $this->items[$index]['warehouse_location'] = '';
                $this->items[$index]['unit_price'] = 0;
                $this->items[$index]['total_amount'] = 0;
                return;
            }

            // Tìm sản phẩm (không phân biệt hoa thường bằng cách dùng LOWER trong SQL)
            $product = null;
            $searchValue = trim($value);
            
            // 1. Nếu có định dạng "Code - Name"
            if (str_contains($searchValue, ' - ')) {
                $code = trim(explode(' - ', $searchValue)[0]);
                $product = Product::whereRaw('LOWER(code) = ?', [strtolower($code)])->first();
            }
            
            // 2. Tìm theo mã chính xác
            if (!$product) {
                $product = Product::whereRaw('LOWER(code) = ?', [strtolower($searchValue)])->first();
            }
            
            // 3. Tìm theo tên chính xác
            if (!$product) {
                $product = Product::whereRaw('LOWER(name) = ?', [strtolower($searchValue)])->first();
            }
            
            if (!$product) return;

            // === ĐÃ TÌM THẤY SẢN PHẨM ===
            $productId = $product->id;
            $this->items[$index]['product_id'] = $productId;
            
            // 1. Luôn điền thông tin cơ bản từ Danh mục sản phẩm trước (Fallback)
            // Lấy UNIT thông minh: Thử Unit -> Box Spec -> Carton Spec
            $this->items[$index]['unit'] = $product->unit ?: ($product->box_spec ?: ($product->carton_spec ?: '-'));
            $this->items[$index]['brand'] = $product->brand ?: '';
            $this->items[$index]['batch_number'] = $product->batch_number ?: '';
            $this->items[$index]['expiry_date'] = $product->expiry_date ? $product->expiry_date->format('Y-m-d') : '';
            $this->items[$index]['warehouse_location'] = $product->location ?: '';
            $this->items[$index]['unit_price'] = $product->price ?: 0;
            $this->calculateTotal($index);

            // 2. Kiểm tra tồn kho thực tế theo lô
            $service = app(InventoryService::class);
            $batches = $service->getAvailableBatches($productId);

            if ($batches->count() > 1) {
                // Có nhiều lô → Mở cửa sổ cho người dùng chọn
                $this->activeItemIndex = $index;
                $this->availableBatches = $batches->toArray();
                $this->showBatchModal = true;
            } elseif ($batches->count() == 1) {
                // Chỉ có 1 lô → Cập nhật thông tin từ lô này (Ghi đè nếu lô có dữ liệu)
                $batch = $batches->first();
                if ($batch->batch_number) {
                    $this->items[$index]['batch_number'] = $batch->batch_number;
                }
                if ($batch->expiry_date) {
                    $this->items[$index]['expiry_date'] = $batch->expiry_date;
                }
                if ($batch->warehouse_location) {
                    $this->items[$index]['warehouse_location'] = $batch->warehouse_location;
                }
            }
        }

        // Thay đổi loại xuất
        if ($name === 'type') {
            $this->items = [];
            if ($value !== 'production') {
                $this->addItem();
            } else {
                $this->loadBomMaterials();
            }
        }

        // Thay đổi thành phẩm trong sản xuất
        if ($name === 'production_product_id' || $name === 'production_quantity') {
            if ($this->type === 'production') {
                $this->loadBomMaterials();
            }
        }

        // Tính toán lại thành tiền nếu thay đổi số lượng, đơn giá, hoặc VAT
        if (str_contains($name, 'items') && (str_ends_with($name, '.quantity') || str_ends_with($name, '.unit_price') || str_ends_with($name, '.vat_rate'))) {
            $parts = explode('.', $name);
            $this->calculateTotal($parts[1]);
        }
    }

    public function calculateTotal($index)
    {
        if (isset($this->items[$index])) {
            $qty = floatval($this->items[$index]['quantity'] ?: 0);
            $price = floatval($this->items[$index]['unit_price'] ?: 0);
            $vat = floatval($this->items[$index]['vat_rate'] ?: 0);
            
            $subtotal = $qty * $price;
            $total = $subtotal + ($subtotal * $vat / 100);
            $this->items[$index]['total_amount'] = $total;
        }
    }

    public function selectBatch($batchIndex)
    {
        if (!isset($this->availableBatches[$batchIndex])) return;

        $batch = $this->availableBatches[$batchIndex];
        $index = $this->activeItemIndex;

        $this->items[$index]['batch_number'] = $batch['batch_number'];
        $this->items[$index]['expiry_date'] = $batch['expiry_date'];
        $this->items[$index]['warehouse_location'] = $batch['warehouse_location'];

        $this->closeBatchModal();
    }

    public function closeBatchModal()
    {
        $this->showBatchModal = false;
        $this->availableBatches = [];
        $this->activeItemIndex = null;
    }

    public function removeItem($index)
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    private function loadBomMaterials()
    {
        $this->items = [];
        if (!$this->production_product_id || $this->production_quantity <= 0) {
            return;
        }

        $service = app(\App\Services\BOMService::class);
        $availability = $service->checkMaterialAvailability($this->production_product_id, $this->production_quantity);

        foreach ($availability['details'] as $detail) {
            $product = \App\Models\Product::find($detail['material_id']);
            if (!$product) continue;
            
            $batch_number = '';
            $expiry_date = '';
            $warehouse_location = $product->location ?: '';

            $invService = app(\App\Services\InventoryService::class);
            $batches = $invService->getAvailableBatches($product->id);
            if ($batches->count() == 1) {
                $batch = $batches->first();
                $batch_number = $batch->batch_number;
                $expiry_date = $batch->expiry_date;
                $warehouse_location = $batch->warehouse_location;
            }
            
            $reqQty = floatval($detail['required']);
            $price = floatval($product->price ?: 0);
            
            $this->items[] = [
                'product_id' => $product->id,
                'product_search' => $product->code . ' - ' . $product->name,
                'unit' => $product->unit ?: ($product->box_spec ?: ($product->carton_spec ?: '-')),
                'brand' => $product->brand ?: '',
                'batch_number' => $batch_number,
                'expiry_date' => $expiry_date,
                'warehouse_location' => $warehouse_location,
                'quantity' => $reqQty,
                'unit_price' => $price,
                'vat_rate' => 0,
                'total_amount' => $reqQty * $price,
                'is_printed' => true,
                
                // Extra fields for rendering BOM UI
                'available_qty' => $detail['available'],
                'is_sufficient' => $detail['is_sufficient']
            ];
        }
    }

    public function save()
    {
        $this->validate([
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.0001',
            'customer_name' => 'nullable|string',
            'receiver_department' => 'nullable|string',
        ]);

        $service = app(InventoryService::class);

        return DB::transaction(function () use ($service) {
            try {
                $stockOut = StockOut::create([
                    'code' => 'SO-' . date('Ymd') . '-' . str_pad(StockOut::count() + 1, 4, '0', STR_PAD_LEFT),
                    'customer_name' => $this->customer_name . ($this->receiver_department ? " ({$this->receiver_department})" : ""),
                    'type' => $this->type,
                    'status' => 'completed',
                    'note' => $this->note,
                    'created_by' => auth()->id(),
                ]);

                foreach ($this->items as $item) {
                    StockOutItem::create([
                        'stock_out_id' => $stockOut->id,
                        'product_id' => $item['product_id'],
                        'batch_number' => $item['batch_number'],
                        'expiry_date' => $item['expiry_date'] ?: null,
                        'warehouse_location' => $item['warehouse_location'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'vat_rate' => $item['vat_rate'],
                        'total_amount' => $item['total_amount'],
                    ]);

                    $service->export(
                        $item['product_id'],
                        $item['quantity'],
                        'stock_out',
                        $stockOut->id,
                        $this->note,
                        $item['batch_number'],
                        $item['expiry_date'] ?: null,
                        $item['warehouse_location']
                    );
                }

                session()->flash('success', 'Xuất kho thành công!');
                $this->reset(['items', 'customer_name', 'receiver_department', 'note']);
                $this->addItem();
            } catch (\Exception $e) {
                session()->flash('error', 'Lỗi: ' . $e->getMessage());
                DB::rollBack();
            }
        });
    }

    public function render()
    {
        $productionProducts = Product::where('status', 'active')
            ->where(function($q) {
                $q->where('type', 'product')
                  ->orWhere('type', 'product_produced')
                  ->orWhere('type', 'Thành phẩm');
            })->orderBy('name')->get();

        return view('livewire.warehouse.stock-out-form', [
            'products' => Product::where('status', 'active')->orderBy('name')->get(),
            'productionProducts' => $productionProducts,
            'locations' => Product::whereNotNull('location')->distinct()->pluck('location'),
            'customers' => Supplier::whereIn('type', ['customer', 'Both', 'both', 'KH'])->orderBy('name')->get(),
        ]);
    }

    public function numberToWords($number)
    {
        if ($number == 0) {
            return 'không đồng';
        }
        
        $number = round($number);
        $result = '';
        $tens_d = array(
            0 => 'lẻ',
            1 => 'mười',
            2 => 'hai mươi',
            3 => 'ba mươi',
            4 => 'bốn mươi',
            5 => 'năm mươi',
            6 => 'sáu mươi',
            7 => 'bảy mươi',
            8 => 'tám mươi',
            9 => 'chín mươi'
        );
        $units_d = array(
            0 => 'không',
            1 => 'một',
            2 => 'hai',
            3 => 'ba',
            4 => 'bốn',
            5 => 'năm',
            6 => 'sáu',
            7 => 'bảy',
            8 => 'tám',
            9 => 'chín'
        );

        $groups = [];
        while ($number > 0) {
            $groups[] = $number % 1000;
            $number = floor($number / 1000);
        }

        $suffixes = ['', 'nghìn', 'triệu', 'tỷ', 'nghìn tỷ', 'triệu tỷ'];
        $words = [];

        foreach ($groups as $i => $group) {
            if ($group == 0) {
                continue;
            }
            
            $group_words = [];
            $hundreds = floor($group / 100);
            $remainder = $group % 100;
            $tens = floor($remainder / 10);
            $units = $remainder % 10;

            if ($hundreds > 0 || count($groups) > 1 && $i > 0 && $group > 0) {
                $group_words[] = $units_d[$hundreds] . ' trăm';
            }

            if ($tens > 1) {
                $group_words[] = $tens_d[$tens];
                if ($units == 1) {
                    $group_words[] = 'mốt';
                } elseif ($units == 5) {
                    $group_words[] = 'lăm';
                } elseif ($units > 0) {
                    $group_words[] = $units_d[$units];
                }
            } elseif ($tens == 1) {
                $group_words[] = 'mười';
                if ($units == 5) {
                    $group_words[] = 'lăm';
                } elseif ($units > 0) {
                    $group_words[] = $units_d[$units];
                }
            } elseif ($tens == 0 && $hundreds > 0 && $units > 0) {
                $group_words[] = 'lẻ ' . $units_d[$units];
            } elseif ($units > 0) {
                $group_words[] = $units_d[$units];
            }

            $group_words[] = $suffixes[$i];
            array_unshift($words, implode(' ', $group_words));
        }

        $result = implode(' ', $words);
        $result = preg_replace('/\s+/', ' ', $result);
        $result = trim($result);
        
        if ($result != '') {
             $result = mb_strtoupper(mb_substr($result, 0, 1, 'UTF-8'), 'UTF-8') . mb_substr($result, 1, null, 'UTF-8');
             $result .= ' đồng chẵn.';
        }
        
        return $result;
    }
}
