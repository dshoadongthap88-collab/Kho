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
    use \App\Traits\ExcelColumnMapper {
        columnKeywords as baseColumnKeywords;
    }

    /**
     * Phiếu nhập kho quan tâm số THỰC NHẬP, không phải tồn kho cuối kỳ — nên đảo
     * thứ tự ưu tiên so với màn Tồn kho. File "Báo cáo tổng hợp Xuất Nhập Tồn"
     * có cả ba cột Thực nhập / Xuất kho / Tồn kho.
     */
    protected function columnKeywords(): array
    {
        $keywords = $this->baseColumnKeywords();

        $keywords['quantity'] = [
            'soluongnhap', 'thucnhap', 'thucnhan', 'soluong', 'quantity',
            'khoiluong', 'soluongton', 'tonkho', '=sl', '=qty', '=kl', '=ton',
        ];

        return $keywords;
    }

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
    public $activeImportTab = 'excel';
    public $excelFile = null;

    /**
     * Số dòng tối đa còn giữ trực tiếp trong $items (tức là trong snapshot mà
     * trình duyệt phải gửi đi gửi lại mỗi lần bấm). Vượt ngưỡng này thì dữ liệu
     * nằm hẳn ở session phía server, bảng chỉ hiện phần xem trước.
     *
     * 1545 dòng cho snapshot ~600KB — vượt mức Livewire xử lý gọn và làm PHP
     * hết bộ nhớ lúc dựng phản hồi.
     */
    public const MAX_ROWS_IN_SNAPSHOT = 300;

    /** Số dòng ghi mỗi lô khi lưu, để không ôm cả nghìn dòng cùng lúc */
    public const SAVE_CHUNK_SIZE = 200;

    /** Có bao nhiêu dòng đang nằm ở session (file lớn) */
    public $stagedCount = 0;

    // Chỉnh sửa phiếu nhập
    public $showEditModal = false;
    public $editingStockInId = null;
    public $editDate = '';
    public $editSupplier = '';
    public $editType = '';
    public $editNote = '';

    protected $rules = [
        'items.*.quantity' => 'required|numeric|min:0.0001',
    ];

    /**
     * Chạy đầu MỌI request của component này, kể cả request AJAX của Livewire.
     *
     * Phiếu nhập nhiều dòng làm phản hồi phình rất to (1545 dòng ~ 600KB
     * snapshot cộng phần HTML render lại). Với memory_limit 128M mặc định,
     * PHP chết ngay lúc dựng phản hồi (Response.php) ở khoảng 700 dòng.
     * Tiến trình chết giữa chừng trả về phản hồi cụt, Livewire đọc phải dữ
     * liệu dở dang rồi báo CorruptComponentPayloadException — thông báo đó
     * gây hiểu nhầm là dữ liệu bị sửa đổi, thực chất là hết bộ nhớ.
     */
    public function boot()
    {
        @ini_set('memory_limit', '512M');
        @set_time_limit(300);
    }

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
                $product = Product::where('code', $code)->first();
            }
            
            if (!$product) {
                $product = Product::where('code', $searchValue)->first();
            }
            
            if (!$product) {
                $product = Product::where('name', $searchValue)->first();
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
            $this->items[$index]['product_search'] = $product->code . ' - ' . $product->name;
            $this->items[$index]['product_name'] = $product->name;
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

    /**
     * Chuẩn hoá số tiền, quan trọng nhất là khử SỐ KHÔNG ÂM (-0.0).
     *
     * Vật tư có tồn âm (vd -10) nhân với đơn giá 0 cho ra -0.0 trong PHP.
     * json_encode ghi giá trị đó là "-0", nhưng khi Livewire nhận lại rồi mã
     * hoá lần nữa để kiểm tra thì ra "0" — lệch đúng một ký tự và checksum
     * không khớp, sinh ra CorruptComponentPayloadException. Thông báo lỗi nói
     * là "dữ liệu bị sửa đổi" nên rất dễ đi lạc hướng.
     */
    private function normalizeAmount($value): float
    {
        $value = (float) $value;

        return $value == 0.0 ? 0.0 : $value;
    }

    public function calculateTotal($index)
    {
        $qty = floatval($this->items[$index]['quantity'] ?? 0);
        $price = floatval($this->items[$index]['unit_price'] ?? 0);
        $vat = floatval($this->items[$index]['vat_rate'] ?? 0);

        $subtotal = $qty * $price;
        $this->items[$index]['total_amount'] = $this->normalizeAmount($subtotal + ($subtotal * $vat / 100));
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

    /**
     * Suy ra [mã, tên] cho một vật tư mới từ dữ liệu dòng nhập.
     * Ưu tiên new_code/new_name; nếu thiếu thì tách từ product_search theo định dạng "Mã - Tên".
     */
    protected function deriveNewProductIdentity(array $item): array
    {
        $code = !empty($item['new_code']) ? trim($item['new_code']) : '';
        $name = !empty($item['new_name']) ? trim($item['new_name']) : '';

        if ($code === '' || $name === '') {
            $search = trim($item['product_search'] ?? '');
            if (str_contains($search, ' - ')) {
                $parts = explode(' - ', $search);
                $code = trim($parts[0]);
                $name = trim($parts[1]);
            } elseif ($search !== '') {
                $code = strtoupper($search);
                $name = $search;
            }
        }

        return [$code, $name];
    }

    /**
     * Stage items vào server session trước khi save.
     * Giải pháp bypass PHP max_input_vars (mặc định 1000) khi có nhiều dòng.
     * Livewire vẫn giữ $this->items đầy đủ trên server — chỉ cần lưu lại vào session.
     */
    /**
     * Đưa dữ liệu import lớn vào session, chỉ để lại phần xem trước trên bảng.
     */
    private function stageLargeImport(): void
    {
        $total = count($this->items);

        if ($total <= self::MAX_ROWS_IN_SNAPSHOT) {
            $this->stagedCount = 0;
            session()->forget($this->stagedKey());
            return;
        }

        session([$this->stagedKey() => $this->items]);
        $this->stagedCount = $total;

        // Chỉ giữ lại phần đầu để người dùng đối chiếu; phần còn lại vẫn được
        // lưu đầy đủ vì lấy từ session lúc bấm Lưu.
        $this->items = array_slice($this->items, 0, self::MAX_ROWS_IN_SNAPSHOT);
    }

    private function stagedKey(): string
    {
        return 'stock_in_staged_items_' . auth()->id();
    }

    /** Toàn bộ dòng sẽ được ghi: ưu tiên dữ liệu đầy đủ ở session */
    private function rowsToSave(): array
    {
        $staged = session($this->stagedKey());

        return !empty($staged) ? $staged : $this->items;
    }

    public function stageAndSave()
    {
        // Chỉ cất lại khi CHƯA có bản đầy đủ ở session. Nếu file lớn đã được
        // stageLargeImport() cất từ trước thì $this->items lúc này chỉ là phần
        // xem trước — ghi đè lên session sẽ làm mất hầu hết dòng.
        if (empty(session($this->stagedKey())) && !empty($this->items)) {
            session([$this->stagedKey() => $this->items]);
        }

        $this->save();
    }

    public function save()
    {
        // Nếu items đã được staging vào session thì đọc từ đó để bypass max_input_vars
        $stagedItems = session('stock_in_staged_items_' . auth()->id());
        if (!empty($stagedItems)) {
            $this->items = $stagedItems;
            session()->forget('stock_in_staged_items_' . auth()->id());
        }

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

            // Nếu là vật tư mới (chưa có product_id), đảm bảo mã & tên suy ra được đều không rỗng
            // (tránh lỗi CSDL do tạo sản phẩm với mã/tên rỗng hoặc đụng khoá duy nhất house_id+code)
            if (empty($item['product_id'])) {
                [$derivedCode, $derivedName] = $this->deriveNewProductIdentity($item);
                // Mã & tên phải có nội dung thực (ít nhất 1 ký tự chữ hoặc số), tránh tạo vật tư rác "-"
                if (!preg_match('/[\p{L}\p{N}]/u', $derivedCode) || !preg_match('/[\p{L}\p{N}]/u', $derivedName)) {
                    $errorMsg = 'Thiếu mã hoặc tên vật tư ở dòng số ' . ($index + 1) . '. Vui lòng nhập theo định dạng: Mã - Tên.';
                    $this->addError("items.{$index}.product_search", $errorMsg);
                    $this->dispatch('show-error-effect', message: $errorMsg);
                    return;
                }
                if (mb_strlen($derivedCode) > 255 || mb_strlen($derivedName) > 255) {
                    $errorMsg = 'Mã hoặc tên vật tư ở dòng số ' . ($index + 1) . ' quá dài (tối đa 255 ký tự).';
                    $this->addError("items.{$index}.product_search", $errorMsg);
                    $this->dispatch('show-error-effect', message: $errorMsg);
                    return;
                }
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
                // Thông tin phiếu
                'items'                        => 'required|array|min:1',
                'type'                         => 'required|string|max:255',
                'stock_in_date'                => 'nullable|date',
                'supplier_name'                => 'nullable|string|max:255',
                'manufacturer'                 => 'nullable|string|max:255',
                'note'                         => 'nullable|string|max:65535',
                // Số lượng: chặn vượt precision decimal(15,4) của stock_in_items
                'items.*.quantity'             => 'required|numeric|min:0.0001|max:9999999999',
                // Đơn giá: decimal(15,2)
                'items.*.unit_price'           => 'nullable|numeric|min:0|max:999999999999',
                // Thuế suất: decimal(5,2), theo phần trăm 0..100
                'items.*.vat_rate'             => 'nullable|numeric|min:0|max:100',
                // Các trường chuỗi: giới hạn varchar(255)
                'items.*.batch_number'         => 'nullable|string|max:255',
                'items.*.warehouse_location'   => 'nullable|string|max:255',
                'items.*.unit'                 => 'nullable|string|max:255',
                'items.*.new_code'             => 'nullable|string|max:255',
                'items.*.new_name'             => 'nullable|string|max:255',
                'items.*.product_search'       => 'nullable|string|max:511',
                'items.*.expiry_date'          => 'nullable|date',
            ], [
                'items.required'               => 'Vui lòng thêm ít nhất một vật tư vào phiếu nhập.',
                'items.min'                    => 'Vui lòng thêm ít nhất một vật tư vào phiếu nhập.',
                'items.*.quantity.required'    => 'Vui lòng nhập số lượng.',
                'items.*.quantity.numeric'     => 'Số lượng phải là số.',
                'items.*.quantity.min'         => 'Số lượng phải lớn hơn 0.',
                'items.*.quantity.max'         => 'Số lượng quá lớn (tối đa 9.999.999.999).',
                'items.*.unit_price.numeric'   => 'Đơn giá phải là số.',
                'items.*.unit_price.min'       => 'Đơn giá không được âm.',
                'items.*.unit_price.max'       => 'Đơn giá quá lớn.',
                'items.*.vat_rate.numeric'     => 'Thuế suất phải là số.',
                'items.*.vat_rate.max'         => 'Thuế suất không hợp lệ (0 - 100).',
                'items.*.expiry_date.date'     => 'Hạn dùng không đúng định dạng ngày.',
                'items.*.batch_number.max'     => 'Số lô quá dài (tối đa 255 ký tự).',
                'items.*.warehouse_location.max' => 'Vị trí kho quá dài (tối đa 255 ký tự).',
                'items.*.unit.max'             => 'Đơn vị tính quá dài (tối đa 255 ký tự).',
                'supplier_name.max'            => 'Tên nhà cung cấp quá dài (tối đa 255 ký tự).',
                'manufacturer.max'             => 'Tên hãng sản xuất quá dài (tối đa 255 ký tự).',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $firstError = collect($e->errors())->flatten()->first();
            $this->dispatch('show-error-effect', message: $firstError);
            throw $e;
        }

        $service = app(InventoryService::class);

        try {
            return DB::transaction(function () use ($service) {
            $baseCode = 'SI-' . date('Ymd') . '-';
            $nextId = \App\Models\StockIn::max('id') + 1;
            $code = $baseCode . str_pad($nextId, 4, '0', STR_PAD_LEFT);
            while (\App\Models\StockIn::where('code', $code)->exists()) {
                $nextId++;
                $code = $baseCode . str_pad($nextId, 4, '0', STR_PAD_LEFT);
            }

            $stockIn = \App\Models\StockIn::create([
                'code' => $code,
                'supplier_name' => $this->supplier_name,
                'manufacturer' => $this->manufacturer,
                'type' => $this->type,
                'status' => 'completed',
                'stock_in_date' => $this->stock_in_date,
                'marked_received' => $this->marked_received,
                'note' => $this->note,
                'created_by' => auth()->id(),
            ]);

            // Ghi theo lô thay vì ôm cả nghìn dòng cùng lúc. Sau mỗi lô thì
            // giải phóng bộ nhớ Eloquent đã giữ, nhờ vậy RAM đi ngang thay vì
            // tăng đều cho tới lúc vượt memory_limit.
            foreach (array_chunk($this->items, self::SAVE_CHUNK_SIZE) as $chunk) {
            foreach ($chunk as $item) {
                $productId = $item['product_id'];

                // Tự động tạo sản phẩm mới nếu chưa tồn tại
                if (empty($productId)) {
                    // Lấy mã và tên từ new_code / new_name hoặc phân tách từ product_search
                    [$itemCode, $itemName] = $this->deriveNewProductIdentity($item);

                    // Kiểm tra xem sản phẩm có mã này vừa được tạo trong giao dịch hoặc đã có sẵn chưa
                    $existing = Product::where('code', $itemCode)->first();
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
                            'code' => strtoupper($itemCode),
                            'name' => $itemName,
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

                // Tính lại thành tiền phía server và chặn tràn precision decimal(15,2)
                $lineQty   = (float) ($item['quantity'] ?? 0);
                $linePrice = (float) ($item['unit_price'] ?? 0);
                $lineVat   = (float) ($item['vat_rate'] ?? 0);
                $lineSubtotal = $lineQty * $linePrice;
                $lineTotal = $lineSubtotal + ($lineSubtotal * $lineVat / 100);
                $lineTotal = min($lineTotal, 9999999999999.99); // trần decimal(15,2)

                // Tạo StockInItem
                \App\Models\StockInItem::create([
                    'stock_in_id' => $stockIn->id,
                    'product_id' => $productId,
                    'batch_number' => $batchNo,
                    'expiry_date' => $expiry,
                    'warehouse_location' => $item['warehouse_location'] ?? null,
                    'quantity' => $lineQty,
                    'unit_price' => $linePrice,
                    'vat_rate' => $lineVat,
                    'total_amount' => $lineTotal,
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
                
                // Luôn cập nhật tên và đơn vị tính dựa theo dữ liệu import (nếu có mới)
                $updateName = !empty($item['csv_name']) ? trim($item['csv_name']) : (!empty($item['new_name']) ? trim($item['new_name']) : '');
                if (empty($updateName) && !empty($item['product_search'])) {
                    $searchParts = explode(' - ', $item['product_search']);
                    if (count($searchParts) > 1) {
                        $updateName = trim($searchParts[1]);
                    }
                }
                if (!empty($updateName)) {
                    $productUpdates['name'] = $updateName;
                }
                $updateUnit = !empty($item['csv_unit']) ? trim($item['csv_unit']) : (!empty($item['unit']) ? $item['unit'] : '');
                if (!empty($updateUnit)) {
                    $productUpdates['unit'] = $updateUnit;
                }
                
                if (!empty($productUpdates)) {
                    Product::where('id', $productId)->update($productUpdates);
                }

                // Tự động cập nhật Kế hoạch mua hàng (PurchasePlan) — batch query, không query trong loop
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
                        // Dùng update() thay vì save() để tránh trigger booted() model events không cần thiết
                        $plan->saveQuietly();
                    }
                }
            }

            // Hết một lô: bỏ các model Eloquent đã nạp trong lô này để RAM
            // không tăng dần theo số dòng.
            unset($chunk);
            gc_collect_cycles();
            }

            $savedRows = count($this->items);
            session()->flash('success', 'Nhập kho thành công! Các sản phẩm mới đã được tự động thêm vào Danh mục vật tư.');
            $this->dispatch('show-success-effect');
            $this->dispatch('toast',
                message: sprintf('Đã lưu phiếu nhập %s với %d dòng vật tư.', $code, $savedRows),
                icon: '📦',
                type: 'success',
                duration: 4000);
            $this->reset(['items', 'marked_received']);
            $this->stagedCount = 0;
            $this->addItem();
            });
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Luu phieu nhap kho that bai: ' . $e->getMessage(), [
                'exception' => $e,
                'type' => $this->type,
                'item_count' => count($this->items),
            ]);
            session()->flash('error', 'Lưu phiếu nhập kho thất bại: ' . $e->getMessage());
            $this->dispatch('show-error-effect', message: 'Lưu phiếu thất bại: ' . $e->getMessage());
        }
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

    public function openEditModal()
    {
        if (count($this->selectedIds) !== 1) {
            $this->dispatch('show-error-effect', ['message' => 'Vui lòng chỉ chọn 1 phiếu để sửa.']);
            return;
        }

        $id = $this->selectedIds[0];
        $stockIn = StockIn::find($id);
        if (!$stockIn) {
            $this->dispatch('show-error-effect', ['message' => 'Không tìm thấy phiếu.']);
            return;
        }

        $this->editingStockInId = $id;
        $this->editDate = $stockIn->stock_in_date ? $stockIn->stock_in_date->format('Y-m-d') : $stockIn->created_at->format('Y-m-d');
        $this->editSupplier = $stockIn->supplier_name ?: $stockIn->manufacturer;
        $this->editType = $stockIn->type;
        $this->editNote = $stockIn->note;

        $this->showEditModal = true;
    }

    public function saveEdit()
    {
        $this->validate([
            'editDate' => 'required|date',
            'editType' => 'required|string',
        ]);

        $stockIn = StockIn::find($this->editingStockInId);
        if ($stockIn) {
            $stockIn->update([
                'stock_in_date' => $this->editDate,
                'supplier_name' => $this->editSupplier,
                'type' => $this->editType,
                'note' => $this->editNote,
            ]);
            
            $this->showEditModal = false;
            $this->selectedIds = [];
            $this->dispatch('show-edit-success-effect');
        }
    }

    public function importExcelData()
    {
        // File nhiều nghìn dòng dễ chạm giới hạn thời gian và bộ nhớ mặc định.
        // Nếu hosting khoá ini_set thì hai lệnh này im lặng bỏ qua, không lỗi.
        @set_time_limit(600);
        @ini_set('memory_limit', '512M');

        $this->validate([
            'excelFile' => 'required|file|mimes:csv,txt,xlsx,xls|max:10240',
        ], [
            'excelFile.required' => 'Vui lòng chọn tệp tin.',
            'excelFile.mimes' => 'Tệp tin phải có định dạng CSV, XLSX hoặc XLS.',
        ]);

        try {
            // SheetReader giới hạn số cột đọc vào — xem chú thích trong lớp đó
            $data = \Maatwebsite\Excel\Facades\Excel::toArray(new \App\Imports\SheetReader, $this->excelFile);
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
                    $str = mb_strtolower((string) $str, 'UTF-8');
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

                // Tìm dòng tiêu đề bằng cách chấm điểm, dùng chung logic với các
                // luồng nhập Excel khác (App\Traits\ExcelColumnMapper). Nhờ vậy
                // đọc được cả file báo cáo có tiêu đề 2 tầng (ô gộp).
                $header = $this->resolveHeader($rows);

                // Không nhận ra cột nào => báo rõ thay vì im lặng nhập 0 dòng
                if ($header === null) {
                    session()->flash('error', 'Không nhận ra dòng tiêu đề trong file. Vui lòng đảm bảo file có cột "Mã vật tư" và ít nhất một cột nữa (Tên vật tư, ĐVT, Số lượng...).');
                    $this->dispatch('toast',
                        message: 'Không nhận ra dòng tiêu đề trong tệp. Cần có cột Mã vật tư.',
                        icon: '❌', type: 'error', duration: 6000);
                    return;
                }

                $headerRowIndex = $header['dataStartRow'] - 1;
                $indices = $header['columns'] + [
                    'batch_number'       => $header['columns']['batch']      ?? null,
                    'expiry_date'        => $header['columns']['expiry']     ?? null,
                    'warehouse_location' => $header['columns']['location']   ?? null,
                ];

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

                    $unitVal = isset($indices['unit']) && isset($row[$indices['unit']]) ? trim($row[$indices['unit']]) : '';

                    $this->items[] = [
                        'product_id' => $product?->id ?? '',
                        'product_search' => $product ? ($product->code . ' - ' . $product->name) : ($newCode . ($newName ? ' - ' . $newName : '')),
                        'new_code' => $newCode,
                        'new_name' => $newName,
                        'csv_name' => $nameVal, // Capture the name from CSV directly
                        'csv_unit' => $unitVal, // Capture the unit from CSV directly
                        'batch_number' => $batchVal,
                        'expiry_date' => $expiryVal,
                        'warehouse_location' => $locationVal ?: ($product?->location ?? ''),
                        'quantity' => $qtyVal,
                        'unit' => $unitVal ?: ($product?->unit ?: ($product?->box_spec ?: ($product?->carton_spec ?: 'Cái'))),
                        'unit_price' => $priceVal,
                        'vat_rate' => 0,
                        'total_amount' => $this->normalizeAmount(is_numeric($qtyVal) ? ($qtyVal * $priceVal) : 0)
                    ];
                }

                // Phiếu nhập kho không thể nhập số lượng 0 hoặc âm — validate
                // chặn cả phiếu nếu còn sót. File xuất từ phần mềm kế toán có
                // rất nhiều dòng tồn 0, nên lọc luôn ở đây và báo rõ số lượng
                // bị bỏ, thay vì để người dùng gặp "Số lượng phải lớn hơn 0
                // (and 232 more errors)" mà không biết dòng nào.
                $before = count($this->items);
                $this->items = array_values(array_filter($this->items, function ($item) {
                    return is_numeric($item['quantity']) && (float) $item['quantity'] > 0;
                }));
                $skippedZero = $before - count($this->items);

                if (empty($this->items)) {
                    $this->addItem();
                }

                $this->showImportModal = false;
                $this->excelFile = null;

                // File lớn: cất toàn bộ dòng vào session, chỉ giữ lại phần xem
                // trước trong $items. Nhờ vậy snapshot gửi qua lại chỉ vài chục
                // KB thay vì 600KB, và không còn hết bộ nhớ khi dựng phản hồi.
                $this->stageLargeImport();

                // Báo rõ số dòng đọc được. Chỉ nói "thành công" thì người dùng
                // không biết file 1500 dòng có vào đủ hay không.
                $loaded = count($this->items);
                $msg = sprintf('Đã đọc %d dòng từ tệp Excel.', $loaded);
                if ($skippedZero > 0) {
                    $msg .= sprintf(
                        ' Bỏ qua %d dòng có số lượng bằng 0 hoặc âm — phiếu nhập kho chỉ nhận số lượng lớn hơn 0.',
                        $skippedZero
                    );
                }
                $msg .= ' Những ô thiếu thông tin được báo màu cam để anh/chị bổ sung.';
                session()->flash('success', $msg);
                $this->dispatch('toast',
                    message: $skippedZero > 0
                        ? sprintf('Đã nạp %d dòng. Bỏ %d dòng số lượng 0 hoặc âm.', $loaded, $skippedZero)
                        : sprintf('Đã nạp %d dòng từ tệp Excel vào phiếu nhập.', $loaded),
                    icon: '📥',
                    type: 'success');
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Lỗi nhập tệp Excel: ' . $e->getMessage());
            $this->dispatch('toast',
                message: 'Lỗi đọc tệp Excel: ' . $e->getMessage(),
                icon: '❌',
                type: 'error',
                duration: 6000);
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
                'total_amount' => $this->normalizeAmount(is_numeric($qtyVal) ? ($qtyVal * $priceVal) : 0)
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

        // Không truyền toàn bộ products vào view — autocomplete dùng wire:model.debounce
        // Chỉ load brands cho dropdown filter (cached 5 phút)
        return view('livewire.warehouse.stock-in-form', [
            // Chỉ lấy các cột cần cho datalist autocomplete, cache 3 phút theo house + type
            'products' => \Illuminate\Support\Facades\Cache::remember(
                'stock_in_products_' . (session('current_house') ?? 0) . '_' . ($this->type === 'import_material' ? 'material' : 'other'),
                180,
                fn() => $productQuery->orderBy('code')->get(['id', 'code', 'name', 'unit', 'price', 'location', 'box_spec', 'carton_spec'])
            ),
            'suppliers' => Supplier::orderBy('name')->get(['id', 'name']),
            'brands' => \Illuminate\Support\Facades\Cache::remember(
                'product_brands_' . (session('current_house') ?? 0), 300,
                fn() => Product::whereNotNull('brand')->distinct()->pluck('brand')
            ),
            'allOnPage' => $allOnPage,
            'idsOnPage' => $idsOnPage,
        ]);
    }
}



