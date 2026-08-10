<?php

namespace App\Livewire\Warehouse;

use App\Models\Product;
use App\Models\Supplier;
use App\Models\StockIn;
use App\Services\InventoryService;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Livewire\WithPagination;
use App\Exports\StockInListExport;
use Maatwebsite\Excel\Facades\Excel;

use Livewire\WithFileUploads;

class StockInForm extends Component
{
    use WithPagination;
    use WithFileUploads;

    public $items = [];
    public $activeTab = 'form'; // 'form' hoặc 'list'
    public $listDateFrom = '';
    public $listDateTo = '';
    public $listSearch = '';
    public $selectedIds = [];
    public $printItems = []; // Danh sách các phiếu nhập để in hàng loạt
    public $supplier_name = '';
    public $manufacturer = '';
    public $note = '';
    public $type = 'purchase_produced';

    public $stock_in_date = '';
    public $marked_received = false;
    // Modal tạo nhanh sản phẩm
    public $showProductModal = false;
    public $newPCode = '';
    public $newPName = '';
    public $newPUnit = 'Cái';

    // Nhập tệp đa phương thức tự động
    public $showImportModal = false;
    public $excelFile = null;

    protected $rules = [
        'items.*.quantity' => 'required|numeric|min:0.0001',
    ];

    public function mount()
    {
        $this->listDateFrom = now()->startOfMonth()->format('Y-m-d');
        $this->listDateTo = now()->format('Y-m-d');
        $this->stock_in_date = now()->format('Y-m-d');

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

        $hasProduct = !empty($lastItem['product_id']) || !empty($lastItem['new_code']) || !empty($lastItem['product_search']);

        return $hasProduct && 
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
            'new_code' => '',
            'new_name' => '',
            'batch_number' => '',
            'expiry_date' => '',
            'warehouse_location' => '',
            'quantity' => 1,
		'unit' => 'Cái',
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
                $this->items[$index]['new_code'] = '';
                $this->items[$index]['new_name'] = '';
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
            
            if (!$product) {
                // === KHÔNG TÌM THẤY SẢN PHẨM TRÊN HỆ THỐNG => CHO PHÉP NHẬP LINH HOẠT VẬT TƯ MỚI ===
                $this->items[$index]['product_id'] = '';
                if (str_contains($searchValue, ' - ')) {
                    $parts = explode(' - ', $searchValue);
                    $this->items[$index]['new_code'] = trim($parts[0]);
                    $this->items[$index]['new_name'] = trim($parts[1]);
                } else {
                    $this->items[$index]['new_code'] = strtoupper($searchValue);
                    $this->items[$index]['new_name'] = $searchValue;
                }
                return;
            }

            // === ĐÃ TÌM THẤY SẢN PHẨM ===
            $this->items[$index]['product_id'] = $product->id;
            $this->items[$index]['new_code'] = '';
            $this->items[$index]['new_name'] = '';
            
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
        $prefix = ($this->type === 'import_material') ? 'NVL' : 'SP';
        
        $count = Product::where('code', 'like', $prefix . '%')->count() + 1;
        $this->newPCode = $prefix . str_pad($count, 4, '0', STR_PAD_LEFT);
        
        while (Product::where('code', $this->newPCode)->exists()) {
            $count++;
            $this->newPCode = $prefix . str_pad($count, 4, '0', STR_PAD_LEFT);
        }

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
        ], [
            'newPCode.required' => 'Mã sản phẩm không được để trống.',
            'newPCode.unique' => 'Mã sản phẩm này đã tồn tại.',
            'newPName.required' => 'Vui lòng nhập tên sản phẩm.',
            'newPUnit.required' => 'Vui lòng nhập đơn vị tính.',
        ]);

        $productType = 'product_purchased'; // default
        if ($this->type === 'import_material') {
            $productType = 'material';
        } elseif ($this->type === 'production') {
            $productType = 'product_purchased';
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
		'unit' => 'Cái',
                'unit_price' => $product?->price ?: 0,
                'vat_rate' => 0,
                'total_amount' => $product?->price ?: 0
            ];
        }
    }

    public function save()
    {
        // Validate base requirement before anything
        if (empty($this->items)) {
            $this->addError('general', 'Vui lòng thêm ít nhất một sản phẩm vào phiếu nhập.');
            $this->addItem();
            return;
        }

        // Kiểm tra hợp lệ cho từng dòng - KHÔNG filter trước, validate nguyên bản
        foreach ($this->items as $index => $item) {
            // Kiểm tra xem dòng có thông tin sản phẩm hợp lệ không
            $hasProduct = !empty($item['product_id']) ||
                          (!empty($item['new_code']) && !empty($item['new_name'])) ||
                          (!empty($item['product_search']) && str_contains($item['product_search'], ' - '));

            if (!$hasProduct) {
                $errorMsg = 'Vui lòng chọn vật tư hoặc nhập mã/tên vật tư hợp lệ (định dạng: Mã - Tên) ở dòng số ' . ($index + 1);
                $this->addError("items.{$index}.product_search", $errorMsg);
                $this->dispatch('show-error-effect', message: $errorMsg);
                return;
            }
        }

        // Tiền xử lý dữ liệu trước khi validate (để tránh lỗi validate do chuỗi rỗng)
        foreach ($this->items as &$item) {
            if (isset($item['expiry_date']) && trim($item['expiry_date']) === '') {
                // Nếu hạn dùng bị bỏ trống, mặc định 365 ngày kể từ ngày nhập
                $baseDate = !empty($this->stock_in_date) ? $this->stock_in_date : date('Y-m-d');
                $item['expiry_date'] = date('Y-m-d', strtotime($baseDate . ' + 365 days'));
            }
            if (isset($item['batch_number']) && trim($item['batch_number']) === '') {
                $item['batch_number'] = null; // null để nullable pass
            }
            if (isset($item['warehouse_location']) && trim($item['warehouse_location']) === '') {
                $item['warehouse_location'] = null;
            }
            if (isset($item['unit_price']) && trim($item['unit_price']) === '') {
                $item['unit_price'] = 0;
            }
        }
        unset($item);

        try {
            $this->validate([
                'items.*.quantity' => 'required|numeric|min:0.0001',
                'supplier_name' => 'nullable|string',
                'items.*.batch_number' => 'nullable|string',
                'items.*.expiry_date' => 'nullable|date',
                'items.*.warehouse_location' => 'nullable|string',
                'items.*.unit_price' => 'nullable|numeric|min:0',
            ], [
                'items.*.quantity.required' => 'Vui lòng nhập số lượng.',
                'items.*.quantity.min' => 'Số lượng phải lớn hơn 0.',
                'items.*.expiry_date.date' => 'Hạn dùng không đúng định dạng ngày.',
                'items.*.unit_price.numeric' => 'Đơn giá phải là số.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $firstError = collect($e->errors())->flatten()->first();
            $this->dispatch('show-error-effect', message: $firstError);
            throw $e;
        }

        $service = app(InventoryService::class);

        return DB::transaction(function () use ($service) {
            $stockIn = \App\Models\StockIn::create([
                'code' => 'SI-' . date('Ymd') . '-' . str_pad(\App\Models\StockIn::count() + 1, 4, '0', STR_PAD_LEFT),
                'supplier_name' => $this->supplier_name,
                'manufacturer' => $this->manufacturer,
                'type' => $this->type,
                'status' => 'completed',
            'stock_in_date' => $this->stock_in_date,
            'marked_received' => $this->marked_received,
                'note' => $this->note,
                'created_by' => auth()->id(),
            ]);

            foreach ($this->items as $item) {
                $productId = $item['product_id'];

                // Tự động tạo sản phẩm mới nếu chưa tồn tại
                if (empty($productId)) {
                    // Lấy mã và tên từ new_code / new_name hoặc phân tách từ product_search
                    $code = !empty($item['new_code']) ? trim($item['new_code']) : '';
                    $name = !empty($item['new_name']) ? trim($item['new_name']) : '';

                    if (empty($code) || empty($name)) {
                        $search = trim($item['product_search']);
                        if (str_contains($search, ' - ')) {
                            $parts = explode(' - ', $search);
                            $code = trim($parts[0]);
                            $name = trim($parts[1]);
                        } else {
                            $code = strtoupper($search);
                            $name = $search;
                        }
                    }

                    // Kiểm tra xem sản phẩm có mã này vừa được tạo trong giao dịch hoặc đã có sẵn chưa
                    $existing = Product::whereRaw('LOWER(code) = ?', [strtolower($code)])->first();
                    if ($existing) {
                        $productId = $existing->id;
                    } else {
                        // Quyết định loại sản phẩm tự động dựa trên loại nhập kho
                        $productType = 'product_purchased';
                        if ($this->type === 'import_material') {
                            $productType = 'material';
                        } elseif ($this->type === 'production') {
                            $productType = 'product_purchased';
                        }

                        $newProduct = Product::create([
                            'code' => strtoupper($code),
                            'name' => $name,
                            'unit' => !empty($item['unit']) ? $item['unit'] : 'Cái',
                            'brand' => $this->manufacturer ?: null,
                            'status' => 'active',
                            'type' => $productType,
                            'location' => $item['warehouse_location'] ?? null,
                            'price' => $item['unit_price'] ?? 0,
                        ]);
                        $productId = $newProduct->id;
                    }
                }

                $batchNo = empty($item['batch_number']) ? '-' : $item['batch_number'];
                $expiry = !empty($item['expiry_date']) ? $item['expiry_date'] : null;

                // Tạo StockInItem
                \App\Models\StockInItem::create([
                    'stock_in_id' => $stockIn->id,
                    'product_id' => $productId,
                    'batch_number' => $batchNo,
                    'expiry_date' => $expiry,
                    'warehouse_location' => $item['warehouse_location'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'] ?? 0,
                    'vat_rate' => $item['vat_rate'] ?? 0,
                    'total_amount' => $item['total_amount'] ?? 0,
                ]);

                // Gọi Service để thực hiện nhập kho và tạo giao dịch
                $service->import(
                    $productId,
                    $item['quantity'],
                    'stock_in',
                    $stockIn->id,
                    $this->note,
                    $batchNo,
                    $expiry,
                    $item['warehouse_location'] ?? null
                );
                
                // Cập nhật vị trí mặc định và phân loại của sản phẩm
                $productUpdates = [];
                if (!empty($item['warehouse_location'])) {
                    $productUpdates['location'] = $item['warehouse_location'];
                }
                if ($this->type === 'import_material') {
                    $productUpdates['type'] = 'material';
                }
                
                if (!empty($productUpdates)) {
                    Product::where('id', $productId)->update($productUpdates);
                }

                // Tự động cập nhật Kế hoạch mua hàng (PurchasePlan)
                $pendingPlans = \App\Models\PurchasePlan::where('product_id', $productId)
                    ->whereNotIn('status', ['completed'])
                    ->orderBy('created_at', 'asc')
                    ->get();
                
                $remainingQty = $item['quantity'];
                foreach ($pendingPlans as $plan) {
                    if ($remainingQty <= 0) break;
                    
                    $needed = $plan->proposed_quantity - $plan->delivered_quantity;
                    if ($needed > 0) {
                        $fill = min($needed, $remainingQty);
                        $plan->delivered_quantity += $fill;
                        $remainingQty -= $fill;
                        
                        if ($plan->delivered_quantity >= $plan->proposed_quantity) {
                            $plan->status = 'completed';
                            $plan->notes = 'Đã đủ hàng (phiếu nhập ' . $stockIn->code . ')';
                        } else {
                            $plan->status = 'partial';
                            $plan->notes = 'Đã nhận một phần (phiếu nhập ' . $stockIn->code . ')';
                        }
                        $plan->save();
                    }
                }
            }

            session()->flash('success', 'Nhập kho thành công! Các sản phẩm mới đã được tự động thêm vào Danh mục vật tư.');
            $this->dispatch('show-success-effect');
        $this->reset(['items', 'marked_received']);
            $this->addItem();
        });
    }

    public function exportExcel()
    {
        $query = StockIn::with(['items', 'creator'])
            ->whereBetween('created_at', [$this->listDateFrom . ' 00:00:00', $this->listDateTo . ' 23:59:59'])
            ->where(function($q) {
                $q->where('code', 'like', '%' . $this->listSearch . '%')
                  ->orWhere('supplier_name', 'like', '%' . $this->listSearch . '%');
            });

        $data = $query->latest()->get();
        return Excel::download(new \App\Exports\StockInListExport($data), 'danh_sach_phieu_nhap_kho_' . now()->format('Ymd_His') . '.xlsx');
    }

    public function printSingle($id)
    {
        $this->selectedIds = [(string)$id];
        $this->printSelected();
    }

    public function delete($id)
    {
        $this->selectedIds = [(string)$id];
        $this->deleteSelected();
    }

    public function printSelected()
    {
        if (empty($this->selectedIds)) {
            session()->flash('error', 'Vui lòng chọn ít nhất một phiếu để in.');
            return;
        }

        $this->printItems = StockIn::whereIn('id', $this->selectedIds)
            ->with(['items.product', 'creator'])
            ->get();

        $this->dispatch('trigger-print');
    }

    public function deleteSelected()
    {
        if (empty($this->selectedIds)) {
            return;
        }

        DB::transaction(function () {
            $invService = app(InventoryService::class);
            $stockIns = StockIn::whereIn('id', $this->selectedIds)->with('items')->get();

            foreach ($stockIns as $si) {
                foreach ($si->items as $item) {
                    // Khi xóa phiếu nhập -> Giảm trừ số lượng trong kho
                    // Kiểm tra tồn kho trước khi giảm trừ (tùy chọn nhưng an toàn)
                    $invService->export(
                        $item->product_id,
                        $item->quantity,
                        'reversal',
                        $si->id,
                        "Giảm trừ do xóa phiếu nhập {$si->code}",
                        $item->batch_number,
                        $item->expiry_date,
                        $item->warehouse_location
                    );
                }
                $si->items()->delete();
                $si->delete();
            }
        });

        session()->flash('success', 'Đã xóa ' . count($this->selectedIds) . ' phiếu và giảm trừ tồn kho tương ứng.');
        $this->selectedIds = [];
    }

    public function toggleMarkReceived($id)
    {
        $stockIn = \App\Models\StockIn::findOrFail($id);
        $stockIn->update(['marked_received' => !$stockIn->marked_received]);
        $this->dispatch('show-success-effect');
    }

    public function importExcelData()
    {
        $this->validate([
            'excelFile' => 'required|file|mimes:csv,txt,xlsx,xls|max:10240',
        ], [
            'excelFile.required' => 'Vui lòng chọn tệp tin.',
            'excelFile.mimes' => 'Tệp tin phải có định dạng CSV, XLSX hoặc XLS.',
        ]);

        try {
            $data = \Maatwebsite\Excel\Facades\Excel::toArray(new \stdClass(), $this->excelFile);
            if (!empty($data) && isset($data[0])) {
                $rows = $data[0];
                
                // Khởi tạo chỉ số cột
                $indices = [
                    'code' => null,
                    'name' => null,
                    'quantity' => null,
                    'batch_number' => null,
                    'expiry_date' => null,
                    'warehouse_location' => null,
                    'unit_price' => null
                ];

                $normalize = function($str) {
                    $str = mb_strtolower($str, 'UTF-8');
                    $str = preg_replace('/[áàảãạăắằẳẵặâấầẩẫậ]/u', 'a', $str);
                    $str = preg_replace('/[éèẻẽẹêếềểễệ]/u', 'e', $str);
                    $str = preg_replace('/[íìỉĩị]/u', 'i', $str);
                    $str = preg_replace('/[óòỏõọôốồổỗộơớờởỡợ]/u', 'o', $str);
                    $str = preg_replace('/[úùủũụưứừửữự]/u', 'u', $str);
                    $str = preg_replace('/[ýỳỷỹỵ]/u', 'y', $str);
                    $str = preg_replace('/[đ]/u', 'd', $str);
                    $str = preg_replace('/[^a-z0-9]/', '', $str);
                    return $str;
                };

                $parseQuantity = function($val) {
                    if (is_numeric($val)) return floatval($val);
                    $val = trim((string)$val);
                    if ($val === '' || $val === '-') return '';
                    $val = preg_replace('/[^\d.,]/', '', $val);
                    if (str_contains($val, ',') && str_contains($val, '.')) {
                        $lastComma = strrpos($val, ',');
                        $lastDot = strrpos($val, '.');
                        if ($lastComma > $lastDot) {
                            $val = str_replace('.', '', $val);
                            $val = str_replace(',', '.', $val);
                        } else {
                            $val = str_replace(',', '', $val);
                        }
                    } elseif (str_contains($val, ',')) {
                        $parts = explode(',', $val);
                        if (count($parts) == 2 && (strlen($parts[1]) == 1 || strlen($parts[1]) == 2)) {
                            $val = str_replace(',', '.', $val);
                        } else {
                            $val = str_replace(',', '', $val);
                        }
                    }
                    return floatval($val);
                };

                // Tìm dòng tiêu đề động (quét 15 dòng đầu tiên)
                $headerRowIndex = 0;
                $bestMatchCount = 0;
                $bestIndices = [];

                // Quét 15 dòng đầu tiên để tìm dòng chứa tiêu đề
                foreach (array_slice($rows, 0, 15) as $rowIndex => $potentialHeader) {
                    $currentIndices = [
                        'code' => null, 'name' => null, 'quantity' => null,
                        'batch_number' => null, 'expiry_date' => null,
                        'warehouse_location' => null, 'unit_price' => null
                    ];
                    $matchCount = 0;

                    foreach ($potentialHeader as $colIndex => $colName) {
                        if (empty($colName)) continue;
                        $norm = $normalize($colName);
                        
                        if ($currentIndices['code'] === null && (str_contains($norm, 'masanpham') || str_contains($norm, 'mavattu') || str_contains($norm, 'code') || $norm === 'ma' || str_contains($norm, 'mahh') || str_contains($norm, 'mavt') || str_contains($norm, 'item'))) {
                            $currentIndices['code'] = $colIndex; $matchCount++;
                        } elseif ($currentIndices['name'] === null && (str_contains($norm, 'tensanpham') || str_contains($norm, 'tenvattu') || str_contains($norm, 'name') || $norm === 'ten' || $norm === 'hanghoa' || str_contains($norm, 'tenhh') || str_contains($norm, 'tenvt') || str_contains($norm, 'mota') || str_contains($norm, 'description'))) {
                            $currentIndices['name'] = $colIndex; $matchCount++;
                        } elseif ($currentIndices['quantity'] === null && (str_contains($norm, 'soluong') || str_contains($norm, 'quantity') || str_contains($norm, 'sl') || $norm === 'qty' || str_contains($norm, 'thucnhan') || str_contains($norm, 'khoiluong') || str_contains($norm, 'kl'))) {
                            $currentIndices['quantity'] = $colIndex; $matchCount++;
                        } elseif ($currentIndices['batch_number'] === null && (str_contains($norm, 'solo') || str_contains($norm, 'batch') || str_contains($norm, 'macodencc') || str_contains($norm, 'lo'))) {
                            $currentIndices['batch_number'] = $colIndex; $matchCount++;
                        } elseif ($currentIndices['expiry_date'] === null && (str_contains($norm, 'handung') || str_contains($norm, 'hansudung') || str_contains($norm, 'expiry') || str_contains($norm, 'hsd'))) {
                            $currentIndices['expiry_date'] = $colIndex; $matchCount++;
                        } elseif ($currentIndices['warehouse_location'] === null && (str_contains($norm, 'vitri') || str_contains($norm, 'location') || str_contains($norm, 'kho'))) {
                            $currentIndices['warehouse_location'] = $colIndex; $matchCount++;
                        } elseif ($currentIndices['unit_price'] === null && (str_contains($norm, 'dongia') || str_contains($norm, 'price') || str_contains($norm, 'unitprice') || str_contains($norm, 'gia'))) {
                            $currentIndices['unit_price'] = $colIndex; $matchCount++;
                        }
                    }

                    if ($matchCount > $bestMatchCount) {
                        $bestMatchCount = $matchCount;
                        $bestIndices = $currentIndices;
                        $headerRowIndex = $rowIndex;
                    }
                }

                $indices = $bestIndices;

                // Loại bỏ dòng tiêu đề và các dòng trước đó
                $rows = array_slice($rows, $headerRowIndex + 1);

                // Loại bỏ dòng trống cuối cùng nếu có trước khi thêm dữ liệu mới
                $lastIndex = count($this->items) - 1;
                if ($lastIndex >= 0 && empty($this->items[$lastIndex]['product_id']) && empty($this->items[$lastIndex]['new_code'])) {
                    unset($this->items[$lastIndex]);
                    $this->items = array_values($this->items);
                }

                foreach ($rows as $row) {
                    $codeVal = isset($indices['code']) && isset($row[$indices['code']]) ? trim($row[$indices['code']]) : '';
                    $nameVal = isset($indices['name']) && isset($row[$indices['name']]) ? trim($row[$indices['name']]) : '';
                    $qtyRaw = isset($indices['quantity']) && isset($row[$indices['quantity']]) ? $row[$indices['quantity']] : '';
                    $qtyVal = $parseQuantity($qtyRaw);
                    
                    if (empty($codeVal) && empty($nameVal) && empty($qtyVal)) continue;

                    // Thử tìm sản phẩm
                    $product = null;
                    if (!empty($codeVal)) {
                        $product = Product::where('code', $codeVal)->first();
                    }
                    if (!$product && (!empty($codeVal) || !empty($nameVal))) {
                        $searchTerm = !empty($codeVal) ? $codeVal : $nameVal;
                        $product = Product::where('code', $searchTerm)
                            ->orWhere('name', 'like', '%' . $searchTerm . '%')
                            ->first();
                    }

                    $batchVal = isset($indices['batch_number']) && isset($row[$indices['batch_number']]) ? trim($row[$indices['batch_number']]) : '';
                    
                    $expiryVal = '';
                    if (isset($indices['expiry_date']) && isset($row[$indices['expiry_date']])) {
                        $dateStr = trim($row[$indices['expiry_date']]);
                        if (!empty($dateStr)) {
                            try {
                                $expiryVal = date('Y-m-d', strtotime(str_replace('/', '-', $dateStr)));
                            } catch (\Exception $ex) {}
                        }
                    }

                    // Nếu hạn dùng trống, mặc định 365 ngày từ ngày nhập kho hiện tại
                    if (empty($expiryVal)) {
                        $baseDate = !empty($this->stock_in_date) ? $this->stock_in_date : date('Y-m-d');
                        $expiryVal = date('Y-m-d', strtotime($baseDate . ' + 365 days'));
                    }

                    $locationVal = isset($indices['warehouse_location']) && isset($row[$indices['warehouse_location']]) ? trim($row[$indices['warehouse_location']]) : '';
                    $priceVal = isset($indices['unit_price']) && isset($row[$indices['unit_price']]) ? floatval($row[$indices['unit_price']]) : ($product?->price ?? 0);

                    $newCode = '';
                    $newName = '';
                    if (!$product) {
                        $newCode = $codeVal;
                        $newName = !empty($nameVal) ? $nameVal : $codeVal;
                    }

                    $this->items[] = [
                        'product_id' => $product?->id ?? '',
                        'product_search' => $product ? ($product->code . ' - ' . $product->name) : ($newCode . ($newName ? ' - ' . $newName : '')),
                        'new_code' => $newCode,
                        'new_name' => $newName,
                        'batch_number' => $batchVal,
                        'expiry_date' => $expiryVal,
                        'warehouse_location' => $locationVal ?: ($product?->location ?? ''),
                        'quantity' => $qtyVal,
                        'unit' => $product?->unit ?: ($product?->box_spec ?: ($product?->carton_spec ?: 'Cái')),
                        'unit_price' => $priceVal,
                        'vat_rate' => 0,
                        'total_amount' => is_numeric($qtyVal) ? ($qtyVal * $priceVal) : 0
                    ];
                }

                if (empty($this->items)) {
                    $this->addItem();
                }

                $this->showImportModal = false;
                $this->excelFile = null;
                session()->flash('success', 'Đồng bộ Excel thành công! Những ô thiếu thông tin đã được báo màu cam để anh/chị bổ sung.');
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Lỗi nhập tệp Excel: ' . $e->getMessage());
        }
    }

    public function importParsedData($rows)
    {
        // Loại bỏ dòng trống cuối cùng nếu có trước khi thêm dữ liệu mới
        $lastIndex = count($this->items) - 1;
        if ($lastIndex >= 0 && empty($this->items[$lastIndex]['product_id']) && empty($this->items[$lastIndex]['new_code']) && empty($this->items[$lastIndex]['product_search'])) {
            unset($this->items[$lastIndex]);
            $this->items = array_values($this->items);
        }

        foreach ($rows as $row) {
            // Chỉ lấy thông tin vật tư dữ liệu thực tế có nội dung trong ảnh
            if (empty($row['code']) && empty($row['scanned_name']) && empty($row['quantity'])) {
                continue; 
            }

            $product = null;
            if (!empty($row['code'])) {
                $product = Product::where('code', trim($row['code']))
                    ->orWhere('name', 'like', trim($row['code']))
                    ->first();
            }

            $qtyVal = !empty($row['quantity']) ? floatval($row['quantity']) : '';
            $priceVal = !empty($row['unit_price']) ? floatval($row['unit_price']) : ($product?->price ?? 0);

            $newCode = '';
            $newName = '';
            if (!$product) {
                $newCode = !empty($row['code']) ? trim($row['code']) : '';
                $newName = !empty($row['name']) ? trim($row['name']) : (!empty($row['scanned_name']) ? trim($row['scanned_name']) : 'Vật tư mới quét');
            }

            $this->items[] = [
                'product_id' => $product?->id ?? '',
                'product_search' => $product ? ($product->code . ' - ' . $product->name) : ($newCode . ' - ' . $newName),
                'new_code' => $newCode,
                'new_name' => $newName,
                'batch_number' => '', // Không lấy dữ liệu khác theo yêu cầu
                'expiry_date' => '',
                'warehouse_location' => '', 
                'quantity' => $qtyVal,
                'unit' => !empty($row['unit']) ? trim($row['unit']) : ($product?->unit ?: ($product?->box_spec ?: ($product?->carton_spec ?: 'Cái'))),
                'unit_price' => $priceVal,
                'vat_rate' => 0,
                'total_amount' => is_numeric($qtyVal) ? ($qtyVal * $priceVal) : 0
            ];
        }

        if (empty($this->items)) {
            $this->addItem();
        }

        $this->showImportModal = false;
        session()->flash('success', 'Nhận diện tệp thành công! Những ô thiếu thông tin đã được báo màu cam để anh/chị bổ sung.');
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

        $allOnPage = StockIn::whereBetween('created_at', [$this->listDateFrom . ' 00:00:00', $this->listDateTo . ' 23:59:59'])
            ->where(function($q) {
                $q->where('code', 'like', '%' . $this->listSearch . '%')
                  ->orWhere('supplier_name', 'like', '%' . $this->listSearch . '%');
            })
            ->latest()
            ->paginate(15);

        $idsOnPage = $allOnPage->pluck('id')->toArray();

        return view('livewire.warehouse.stock-in-form', [
            'products' => $productQuery->orderBy('name')->get(),
            'suppliers' => Supplier::orderBy('name')->get(),
            'brands' => Product::whereNotNull('brand')->distinct()->pluck('brand'),
            'allOnPage' => $allOnPage,
            'idsOnPage' => $idsOnPage,
        ]);
    }
}



