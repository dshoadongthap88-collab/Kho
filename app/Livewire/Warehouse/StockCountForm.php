<?php

namespace App\Livewire\Warehouse;

use App\Models\Inventory;
use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\StockCount;
use App\Models\StockCountItem;
use App\Services\InventoryService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\StockCountTemplateExport;
use App\Imports\StockCountImport;



class StockCountForm extends Component
{
    use WithPagination, WithFileUploads;

    public $activeTab = 'stocktake'; // 'stocktake' | 'sync' | 'daily' | 'periodic' | 'chat_ai'

    // --- Stocktake (Kiểm kê) ---
    public $currentCountId = null;
    public $countNote = '';
    public $countItems = []; // [product_id => actual_quantity, note]
    public $syncResults = [];

    // --- Sync (Đòng bộ tồn kho) ---
    public $syncCheckResults = [];
    public $syncing = false;

    // --- List ---
    public $listSearch = '';
    public $selectedStockCounts = [];
    public $excelFile;
    public $locationFilter = '';
    
    // --- Printing ---
    public $isPrintingMultiple = false;
    public $printBatchItems = [];
    public $printBatchCodes = [];

    // --- Chat AI ---
    public $chatInput = '';
    public $chatMessages = [];

    protected $listeners = [];

    public function mount()
    {
        // Tự động vá cấu trúc (Self-Healing) cho bảng stock_count_items
        try {
            if (\Schema::hasColumn('stock_count_items', 'physical_quantity')) {
                DB::statement("ALTER TABLE stock_count_items MODIFY COLUMN physical_quantity DECIMAL(15, 4) NULL DEFAULT 0");
            } else {
                \Schema::table('stock_count_items', function (\Illuminate\Database\Schema\Blueprint $table) {
                    $table->decimal('physical_quantity', 15, 4)->nullable()->default(0);
                });
            }
        } catch (\Exception $e) {
            // Bỏ qua nếu có lỗi phát sinh do môi trường khác nhau
        }

        // Khởi tạo Chat AI
        $this->chatMessages = [
            [
                'sender' => 'ai',
                'text' => "Xin chào! Tôi là Trợ lý AI Kiểm kê kho. Bạn có thể gõ:
- **\"Kiểm kê hôm nay\"** để tự động tạo/lấy danh sách 10 mã vật tư cần kiểm hôm nay theo vị trí kho.
- **\"[Mã vật tư] còn [Số lượng]\"** (Ví dụ: `P001 còn 25` hoặc `P002 = 10`) để cập nhật số lượng kiểm thực tế vào hệ thống.",
                'timestamp' => date('H:i')
            ]
        ];
    }

    // =====================
    // STOCKTAKE FUNCTIONS
    // =====================

    public function createNewStockCount($type = 'full')
    {
        $code = 'KK-' . date('Ymd') . '-' . str_pad(StockCount::count() + 1, 4, '0', STR_PAD_LEFT);

        DB::transaction(function () use ($code, $type) {
            $stockCount = StockCount::create([
                'code' => $code,
                'status' => 'pending',
                'type' => $type,
                'note' => $this->countNote,
                'created_by' => auth()->id(),
            ]);

            // Lấy tất cả vật tư từ Product Catalog thay vì Inventory để đảm bảo đủ mã
            $products = Product::where(function($q) {
                    $q->whereIn('type', ['material', 'product_purchased', 'product_produced'])
                      ->orWhereNull('type');
                })
                ->orderBy('location')
                ->get();

            // Lọc trùng lặp mã vật tư và tên vật tư
            $uniqueProducts = collect();
            $seenNames = [];
            $seenCodes = [];
            foreach ($products as $p) {
                $pCode = trim($p->code);
                $pName = trim($p->name);
                if (in_array($pCode, $seenCodes) || in_array($pName, $seenNames)) {
                    continue;
                }
                $seenCodes[] = $pCode;
                $seenNames[] = $pName;
                $uniqueProducts->push($p);
            }
            $products = $uniqueProducts;

            foreach ($products as $p) {
                // Đảm bảo tồn tại bản ghi Inventory cho vật tư này (nếu chưa có thì tự động tạo với số lượng 0)
                $inv = Inventory::firstOrCreate(
                    ['product_id' => $p->id],
                    ['quantity' => 0, 'warehouse_location' => $p->location]
                );

                StockCountItem::create([
                    'stock_count_id' => $stockCount->id,
                    'product_id' => $p->id,
                    'system_quantity' => $inv->quantity,
                    'actual_quantity' => null,
                    'physical_quantity' => 0,
                    'difference' => 0,
                ]);
            }

            session()->flash('success', "Đã tạo phiếu kiểm kê {$code} với " . $products->count() . " sản phẩm.");

            $this->selectedStockCounts = [];
            $this->currentCountId = $stockCount->id;
        });
    }

    public function createDailyStockCount()
    {
        // 1. Tìm các sản phẩm chưa được kiểm kê trong 7 ngày qua
        $recentProductIds = StockCountItem::whereHas('stockCount', function ($q) {
                $q->where('created_at', '>=', now()->subDays(7))
                  ->whereIn('status', ['pending', 'completed']);
            })
            ->pluck('product_id')
            ->unique()
            ->toArray();

        // 2. Lấy danh sách ứng viên và sắp xếp theo vị trí A-B-C từ Product Catalog
        $products = Product::whereNotIn('id', $recentProductIds)
            ->where(function($q) {
                $q->whereIn('type', ['material', 'product_purchased', 'product_produced'])
                  ->orWhereNull('type');
            })
            ->orderBy('location')
            ->get();

        if ($products->isEmpty()) {
            // Nếu tất cả đã được kiểm trong 7 ngày, lấy tất cả bất kỳ
            $products = Product::where(function($q) {
                    $q->whereIn('type', ['material', 'product_purchased', 'product_produced'])
                      ->orWhereNull('type');
                })
                ->orderBy('location')
                ->get();
        }

        // Lọc trùng lặp mã vật tư và tên vật tư
        $uniqueProducts = collect();
        $seenNames = [];
        $seenCodes = [];
        foreach ($products as $p) {
            $pCode = trim($p->code);
            $pName = trim($p->name);
            if (in_array($pCode, $seenCodes) || in_array($pName, $seenNames)) {
                continue;
            }
            $seenCodes[] = $pCode;
            $seenNames[] = $pName;
            $uniqueProducts->push($p);
        }
        
        // Giới hạn 10 vật tư
        $products = $uniqueProducts->take(10);

        if ($products->isEmpty()) {
             session()->flash('error', "Không tìm thấy vật tư nào để kiểm kê.");
             return;
        }

        $code = 'KKD-' . date('Ymd') . '-' . str_pad(StockCount::count() + 1, 4, '0', STR_PAD_LEFT);

        DB::transaction(function () use ($code, $products) {
            $stockCount = StockCount::create([
                'code' => $code,
                'status' => 'pending',
                'type' => 'daily',
                'note' => 'Kiểm kê hàng ngày (Tự động bốc ngẫu nhiên 10 mã)',
                'created_by' => auth()->id(),
            ]);

            foreach ($products as $p) {
                $inv = Inventory::firstOrCreate(
                    ['product_id' => $p->id],
                    ['quantity' => 0, 'warehouse_location' => $p->location]
                );

                StockCountItem::create([
                    'stock_count_id' => $stockCount->id,
                    'product_id' => $p->id,
                    'system_quantity' => $inv->quantity,
                    'actual_quantity' => null,
                    'physical_quantity' => 0,
                    'difference' => 0,
                ]);
            }

            $this->currentCountId = $stockCount->id;
            session()->flash('success', "Đã tạo phiếu kiểm kê hàng ngày {$code} với " . $products->count() . " vật tư.");
        });

        $this->selectedStockCounts = [];
        $this->activeTab = 'stocktake';
    }



    public function updateActualQty($itemId, $actualQty)
    {
        $item = StockCountItem::find($itemId);
        if (!$item) return;

        $actual = (float) $actualQty;
        $item->update([
            'actual_quantity' => $actual,
            'difference' => $actual - $item->system_quantity,
        ]);
    }

    public function confirmStockCount($stockCountId)
    {
        $stockCount = StockCount::with('items.product')->findOrFail($stockCountId);

        if ($stockCount->status !== 'pending') {
            session()->flash('error', 'Phiếu kiểm kê này đã được xử lý.');
            return;
        }

        $service = app(InventoryService::class);
        $adjustedCount = 0;

        DB::transaction(function () use ($stockCount, $service, &$adjustedCount) {
            foreach ($stockCount->items as $item) {
                // Chỉ điều chỉnh những dòng có nhập actual_quantity và có chênh lệch
                if ($item->actual_quantity !== null && (float)$item->difference != 0) {
                    $service->adjustQuantity(
                        $item->product_id,
                        (float) $item->actual_quantity,
                        "Điều chỉnh từ phiếu kiểm kê {$stockCount->code}"
                    );
                    $adjustedCount++;
                }
            }

            $stockCount->update(['status' => 'completed']);
        });

        $this->currentCountId = null;
        session()->flash('success', 'Xác nhận kiểm kê và điều chỉnh kho thành công!');
    }

    public function editStockCount($id)
    {
        $this->currentCountId = $id;
        $this->activeTab = 'stocktake';
    }

    public function deleteStockCount($id)
    {
        DB::transaction(function () use ($id) {
            $stockCount = StockCount::findOrFail($id);
            $stockCount->items()->delete();
            $stockCount->delete();
        });
        session()->flash('success', 'Đã xóa phiếu kiểm kê.');
    }

    public function cancelStockCount($id)
    {
        StockCount::findOrFail($id)->update(['status' => 'cancelled']);
        $this->currentCountId = null;
    }

    public function bulkDelete()
    {
        Log::info('Triggered bulkDelete', ['selected' => $this->selectedStockCounts]);

        if (empty($this->selectedStockCounts)) {
            session()->flash('error', 'Vui lòng chọn ít nhất một phiếu để xóa.');
            return;
        }

        try {
            $ids = collect($this->selectedStockCounts)->map(fn($id) => (int)$id)->filter()->toArray();
            
            if (!empty($ids)) {
                DB::transaction(function () use ($ids) {
                    \App\Models\StockCountItem::whereIn('stock_count_id', $ids)->delete();
                    StockCount::whereIn('id', $ids)->delete();
                });
                
                $this->selectedStockCounts = [];
                $this->resetPage();
                session()->flash('success', 'Đã xóa thành công ' . count($ids) . ' phiếu kiểm kê.');
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Lỗi khi xóa: ' . $e->getMessage());
        }
    }

    public function bulkPrint()
    {
        if (empty($this->selectedStockCounts)) {
            session()->flash('error', 'Vui lòng chọn ít nhất một phiếu để in.');
            return;
        }

        $counts = StockCount::with('items.product')->whereIn('id', $this->selectedStockCounts)->get();
        
        $this->printBatchItems = [];
        $this->printBatchCodes = [];
        
        foreach ($counts as $count) {
            $this->printBatchCodes[] = $count->code;
            
            // Sắp xếp các vật tư theo thứ tự kệ A-B-C... trước khi in
            $sortedItems = $count->items->sortBy(function($item) {
                return $item->product->location ?? '';
            });

            foreach ($sortedItems as $item) {
                $this->printBatchItems[] = [
                    'count_code' => $count->code,
                    'product_code' => $item->product->code ?? '-',
                    'product_name' => $item->product->name ?? '-',
                    'system_qty' => $item->system_quantity,
                    'actual_qty' => $item->actual_quantity,
                    'difference' => $item->difference,
                    'location' => $item->product->location ?? '-',
                ];
            }
        }

        $this->isPrintingMultiple = true;
    }

    // =====================
    // PERIODIC FUNCTIONS (EXCEL)
    // =====================

    public function exportPeriodicTemplate()
    {
        try {
            $query = Product::where(function($q) {
                    $q->whereIn('type', ['material', 'product_purchased', 'product_produced'])
                      ->orWhereNull('type');
                })
                ->orderBy('location');

            if ($this->locationFilter) {
                $query->where('location', 'like', "%{$this->locationFilter}%");
            }

            $products = $query->get();

            // Lọc trùng lặp mã vật tư và tên vật tư
            $uniqueProducts = collect();
            $seenNames = [];
            $seenCodes = [];
            foreach ($products as $p) {
                $pCode = trim($p->code);
                $pName = trim($p->name);
                if (in_array($pCode, $seenCodes) || in_array($pName, $seenNames)) {
                    continue;
                }
                $seenCodes[] = $pCode;
                $seenNames[] = $pName;
                $uniqueProducts->push($p);
            }
            $products = $uniqueProducts;
            
            if ($products->isEmpty()) {
                session()->flash('error', 'Không có dữ liệu vật tư để xuất Excel.');
                return;
            }

            $data = $products->map(function($p) {
                $inv = $p->inventory;
                return [
                    'ma_san_pham' => $p->code ?? 'N/A',
                    'ten_san_pham' => $p->name ?? 'N/A',
                    'vi_tri' => $p->location ?? ($inv->warehouse_location ?? '-'),
                    'ton_he_thong' => $inv ? (float)$inv->quantity : 0,
                    'so_luong_thuc_te' => ''
                ];
            });

            $code = 'KKP-' . date('Ymd') . '-' . str_pad(StockCount::count() + 1, 4, '0', STR_PAD_LEFT);
            
            DB::beginTransaction();

            $stockCount = StockCount::create([
                'code' => $code,
                'status' => 'pending',
                'type' => 'periodic',
                'note' => 'Kiểm kê định kỳ (Xuất từ Excel)',
                'created_by' => auth()->id(),
            ]);

            foreach ($products as $p) {
                $inv = Inventory::firstOrCreate(
                    ['product_id' => $p->id],
                    ['quantity' => 0, 'warehouse_location' => $p->location]
                );

                StockCountItem::create([
                    'stock_count_id' => $stockCount->id,
                    'product_id' => $p->id,
                    'system_quantity' => $inv->quantity,
                    'actual_quantity' => null,
                    'physical_quantity' => 0,
                    'difference' => 0,
                ]);
            }

            DB::commit();

            $this->currentCountId = $stockCount->id;

            $fileName = "mau_kiem_ke_" . str_replace('-', '_', $code) . ".xlsx";
            
            // Dọn dẹp bộ đệm đầu ra để tránh các ký tự lạ làm hỏng file
            if (ob_get_level()) ob_end_clean();

            return response()->streamDownload(function() use ($data) {
                echo Excel::raw(new StockCountTemplateExport($data), \Maatwebsite\Excel\Excel::XLSX);
            }, $fileName);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Lỗi xuất Excel kiểm kê: ' . $e->getMessage());
            session()->flash('error', 'Có lỗi xảy ra khi xuất file: ' . $e->getMessage());
        }
    }

    public function importPeriodicResults()
    {
        $this->validate([
            'excelFile' => 'required|mimes:xlsx,xls,csv|max:10240',
        ]);

        if (!$this->currentCountId) {
            session()->flash('error', 'Vui lòng chọn hoặc tạo một phiếu kiểm kê trước khi nhập Excel.');
            return;
        }

        try {
            Excel::import(new StockCountImport($this->currentCountId), $this->excelFile);
            $this->reset('excelFile');
            session()->flash('success', 'Nhập dữ liệu kiểm kê từ Excel thành công!');
        } catch (\Exception $e) {
            session()->flash('error', 'Lỗi khi nhập Excel: ' . $e->getMessage());
        }
    }

    // =====================
    // SYNC FUNCTIONS
    // =====================

    public function checkInventorySync()
    {
        $this->syncCheckResults = [];

        // Tính toán lại số liệu từ lịch sử giao dịch và so sánh với bảng inventories
        $transactions = InventoryTransaction::selectRaw('product_id, SUM(quantity) as calculated_qty')
            ->groupBy('product_id')
            ->get()
            ->keyBy('product_id');

        $inventories = Inventory::all()->keyBy('product_id');

        $results = [];
        foreach ($inventories as $productId => $inv) {
            $calculated = $transactions->get($productId)?->calculated_qty ?? 0;
            $stored = (float) $inv->quantity;
            $diff = $stored - (float) $calculated;

            if (abs($diff) > 0.001) {
                $results[] = [
                    'product_id' => $productId,
                    'product_code' => $inv->product->code ?? 'N/A',
                    'product_name' => $inv->product->name ?? 'N/A',
                    'stored_qty' => $stored,
                    'calculated_qty' => (float) $calculated,
                    'difference' => $diff,
                ];
            }
        }

        $this->syncCheckResults = $results;

        if (empty($results)) {
            session()->flash('success', '✅ Tồn kho đồng bộ hoàn toàn! Không có sai lệch nào giữa bảng tồn và lịch sử giao dịch.');
        }
    }

    public function syncInventory()
    {
        $this->syncing = true;
        $count = 0;

        DB::transaction(function () use (&$count) {
            $transactions = InventoryTransaction::selectRaw('product_id, SUM(quantity) as calculated_qty')
                ->groupBy('product_id')
                ->get()
                ->keyBy('product_id');

            $inventories = Inventory::all();

            foreach ($inventories as $inv) {
                $calculated = (float) ($transactions->get($inv->product_id)?->calculated_qty ?? 0);
                $stored = (float) $inv->quantity;

                if (abs($stored - $calculated) > 0.001) {
                    $inv->quantity = $calculated;
                    $inv->save();

                    // Ghi lại log điều chỉnh
                    InventoryTransaction::create([
                        'product_id' => $inv->product_id,
                        'type' => 'adjust',
                        'quantity' => $calculated - $stored,
                        'note' => '[AUTO SYNC] Đồng bộ tự động: Tính lại từ lịch sử giao dịch.',
                        'created_by' => auth()->id(),
                    ]);

                    $count++;
                }
            }
        });

        $this->syncing = false;
        $this->syncCheckResults = [];
        session()->flash('success', "✅ Đã đồng bộ thành công {$count} sản phẩm. Tồn kho đã được cân bằng lại.");
    }

    public function toggleSelectAll($ids)
    {
        if (count($this->selectedStockCounts) >= count($ids)) {
            $this->selectedStockCounts = [];
        } else {
            $this->selectedStockCounts = collect($ids)->map(fn($id) => (string)$id)->toArray();
        }
    }

    public function render()
    {
        $query = StockCount::with('creator')
            ->orderByDesc('created_at');

        if ($this->listSearch) {
            $query->where('code', 'like', "%{$this->listSearch}%");
        }

        $stockCounts = $query->paginate(10);

        $currentCount = $this->currentCountId
            ? StockCount::with('items.product')->find($this->currentCountId)
            : null;

        if ($currentCount) {
            // Sắp xếp các vật tư theo thứ tự kệ A-B-C... khi hiển thị
            $sortedItems = $currentCount->items->sortBy(function($item) {
                return $item->product->location ?? '';
            });
            $currentCount->setRelation('items', $sortedItems);
        }

        return view('livewire.warehouse.stock-count-form', [
            'stockCounts' => $stockCounts,
            'currentCount' => $currentCount,
        ]);
    }

    // =====================
    // CHAT AI FUNCTIONS
    // =====================

    public function sendChatMessage()
    {
        $text = trim($this->chatInput);
        if (empty($text)) return;

        $this->chatMessages[] = [
            'sender' => 'user',
            'text' => $text,
            'timestamp' => date('H:i')
        ];

        $this->chatInput = '';

        $this->processAIResponse($text);
    }

    protected function processAIResponse($text)
    {
        $textLower = mb_strtolower($text, 'UTF-8');

        // Case 1: Hỏi kiểm kê hôm nay
        if (str_contains($textLower, 'kiểm kê hôm nay') || str_contains($textLower, 'kiem ke hom nay') || str_contains($textLower, 'hôm nay kiểm gì') || str_contains($textLower, 'kiem gi hom nay')) {
            // Kiểm tra xem đã có phiếu kiểm kê hàng ngày (hoặc phiếu pending) của hôm nay chưa
            $todayCount = StockCount::where('type', 'daily')
                ->where('status', 'pending')
                ->whereDate('created_at', date('Y-m-d'))
                ->orderByDesc('created_at')
                ->first();

            if (!$todayCount) {
                // Tạo mới nếu chưa có
                $this->createDailyStockCount();
                $todayCount = StockCount::find($this->currentCountId);
            } else {
                $this->currentCountId = $todayCount->id;
                session()->flash('info', "Đang làm việc trên phiếu kiểm kê có sẵn: {$todayCount->code}");
            }

            if ($todayCount) {
                $items = StockCountItem::with('product')
                    ->where('stock_count_id', $todayCount->id)
                    ->get();

                $reply = "📋 **Danh sách 10 mã cần kiểm kê hôm nay (Phiếu: {$todayCount->code})**:\n\n";
                $sortedItems = $items->sortBy(function($item) {
                    return $item->product->location ?? '';
                });

                foreach ($sortedItems as $index => $item) {
                    $location = $item->product->location ?? 'Không rõ';
                    $code = $item->product->code ?? '-';
                    $name = $item->product->name ?? '-';
                    $sysQty = number_format($item->system_quantity);
                    $actualText = $item->actual_quantity !== null ? number_format($item->actual_quantity) : 'Chưa nhập';
                    
                    $reply .= ($index + 1) . ". Vị trí **{$location}**: `{$code}` - **{$name}** (Tồn HT: `{$sysQty}`, Thực tế: `{$actualText}`)\n";
                }
                
                $reply .= "\n*Bạn có thể gõ ví dụ: `{$sortedItems->first()->product->code} còn 20` để cập nhật số lượng kiểm đếm.*";
            } else {
                $reply = "Không thể khởi tạo phiếu kiểm kê ngày hôm nay. Hãy thử lại.";
            }

            $this->chatMessages[] = [
                'sender' => 'ai',
                'text' => $reply,
                'timestamp' => date('H:i')
            ];
            return;
        }

        // Case 2: Cập nhật số lượng vật tư "[Mã] còn [Số lượng]"
        // Regex bắt mã vật tư và số lượng: (mã) còn/co/=/chỉ còn (số lượng)
        if (preg_match('/([A-Za-z0-9_-]+)\s*(?:còn|con|co|có|=)\s*(\d+(?:\.\d+)?)/iu', $text, $matches)) {
            $productCode = trim($matches[1]);
            $qty = floatval($matches[2]);

            // Tìm sản phẩm theo mã
            $product = Product::whereRaw('LOWER(code) = ?', [strtolower($productCode)])->first();

            if (!$product) {
                $this->chatMessages[] = [
                    'sender' => 'ai',
                    'text' => "❌ Không tìm thấy vật tư nào có mã là `{$productCode}` trên hệ thống.",
                    'timestamp' => date('H:i')
                ];
                return;
            }

            // Tìm phiếu kiểm kê đang mở
            $stockCount = $this->currentCountId 
                ? StockCount::find($this->currentCountId)
                : StockCount::where('status', 'pending')->orderByDesc('created_at')->first();

            if (!$stockCount) {
                $this->chatMessages[] = [
                    'sender' => 'ai',
                    'text' => "⚠️ Không có phiếu kiểm kê nào đang hoạt động. Bạn hãy gõ **\"Kiểm kê hôm nay\"** để tự động tạo phiếu kiểm kê hàng ngày trước.",
                    'timestamp' => date('H:i')
                ];
                return;
            }

            $this->currentCountId = $stockCount->id;

            // Tìm item trong phiếu kiểm kê
            $item = StockCountItem::where('stock_count_id', $stockCount->id)
                ->where('product_id', $product->id)
                ->first();

            if (!$item) {
                // Nếu chưa có trong phiếu (ví dụ phiếu daily giới hạn 10 mã nhưng user muốn kiểm thêm)
                // Lấy số tồn hiện tại
                $inventory = Inventory::where('product_id', $product->id)->first();
                $systemQty = $inventory ? $inventory->quantity : 0;

                $item = StockCountItem::create([
                    'stock_count_id' => $stockCount->id,
                    'product_id' => $product->id,
                    'system_quantity' => $systemQty,
                    'actual_quantity' => $qty,
                    'physical_quantity' => 0,
                    'difference' => $qty - $systemQty,
                ]);

                $diff = $qty - $systemQty;
                $reply = "➕ Đã bổ sung vật tư **{$product->name}** (`{$product->code}`) vào phiếu **{$stockCount->code}** và cập nhật:\n";
                $reply .= "- Tồn hệ thống: **" . number_format($systemQty) . "**\n";
                $reply .= "- Thực tế: **" . number_format($qty) . "**\n";
                $reply .= "- Chênh lệch: **" . ($diff > 0 ? '+' : '') . number_format($diff) . "**";
            } else {
                // Đã có trong phiếu, cập nhật
                $item->update([
                    'actual_quantity' => $qty,
                    'difference' => $qty - $item->system_quantity,
                ]);

                $diff = $qty - $item->system_quantity;
                $reply = "✅ Đã cập nhật kết quả cho **{$product->name}** (`{$product->code}`) trên phiếu **{$stockCount->code}**:\n";
                $reply .= "- Tồn hệ thống: **" . number_format($item->system_quantity) . "**\n";
                $reply .= "- Thực tế: **" . number_format($qty) . "**\n";
                $reply .= "- Chênh lệch: **" . ($diff > 0 ? '+' : '') . number_format($diff) . "**";
            }

            $this->chatMessages[] = [
                'sender' => 'ai',
                'text' => $reply,
                'timestamp' => date('H:i')
            ];
            return;
        }

        // Mặc định phản hồi hướng dẫn
        $this->chatMessages[] = [
            'sender' => 'ai',
            'text' => "🤖 Tôi không nhận diện được cú pháp của bạn. Xin vui lòng thử lại:\n- Gõ *\"Kiểm kê hôm nay\"* để hiển thị danh sách.\n- Gõ *\"[Mã] còn [Số lượng]\"* (VD: `P001 còn 10`) để cập nhật số lượng.",
            'timestamp' => date('H:i')
        ];
    }
}
