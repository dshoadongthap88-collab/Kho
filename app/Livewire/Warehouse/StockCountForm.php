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

    public $activeTab = 'stocktake'; // 'stocktake' | 'sync' | 'daily' | 'periodic'



    // --- Stocktake (Kiểm kê) ---
    public $currentCountId = null;
    public $countNote = '';
    public $countItems = []; // [product_id => actual_quantity, note]
    public $syncResults = [];

    // --- Sync (Đồng bộ tồn kho) ---
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
    }

    // =====================
    // STOCKTAKE FUNCTIONS
    // =====================

    public function createNewStockCount($type = 'full')
    {
        $code = 'KK-' . date('Ymd') . '-' . str_pad(StockCount::count() + 1, 4, '0', STR_PAD_LEFT);

        $stockCount = StockCount::create([
            'code' => $code,
            'status' => 'pending',
            'type' => $type,
            'note' => $this->countNote,
            'created_by' => auth()->id(),
        ]);

        // Thêm tất cả items vào phiếu, sắp xếp theo vị trí kệ A-B-C...
        $inventories = Inventory::with('product')
            ->join('products', 'inventories.product_id', '=', 'products.id')
            ->where('inventories.quantity', '>=', 0)
            ->orderBy('products.location')
            ->select('inventories.*')
            ->get();

        // Lọc trùng lặp mã vật tư và tên vật tư
        $uniqueInventories = collect();
        $seenNames = [];
        $seenCodes = [];
        foreach ($inventories as $inv) {
            if (!$inv->product) continue;
            $pCode = trim($inv->product->code);
            $pName = trim($inv->product->name);
            if (in_array($pCode, $seenCodes) || in_array($pName, $seenNames)) {
                continue;
            }
            $seenCodes[] = $pCode;
            $seenNames[] = $pName;
            $uniqueInventories->push($inv);
        }
        $inventories = $uniqueInventories;

        foreach ($inventories as $inv) {
                StockCountItem::create([
                    'stock_count_id' => $stockCount->id,
                    'product_id' => $inv->product_id,
                    'system_quantity' => $inv->quantity,
                    'actual_quantity' => null,
                    'physical_quantity' => 0,
                    'difference' => 0,
                ]);
            }
        session()->flash('success', "Đã tạo phiếu kiểm kê {$code} với " . $inventories->count() . " sản phẩm.");

        $this->selectedStockCounts = [];
        $this->currentCountId = $stockCount->id;
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

        // 2. Lấy danh sách ứng viên và sắp xếp theo vị trí A-B-C
        $inventories = Inventory::with('product')
            ->join('products', 'inventories.product_id', '=', 'products.id')
            ->whereNotIn('inventories.product_id', $recentProductIds)
            ->where('inventories.quantity', '>=', 0)
            ->orderBy('products.location')
            ->select('inventories.*')
            ->get();

        if ($inventories->isEmpty()) {
            // Nếu tất cả đã được kiểm trong 7 ngày, lấy tất cả bất kỳ
             $inventories = Inventory::with('product')
                ->join('products', 'inventories.product_id', '=', 'products.id')
                ->where('inventories.quantity', '>=', 0)
                ->orderBy('products.location')
                ->select('inventories.*')
                ->get();
        }

        // Lọc trùng lặp mã vật tư và tên vật tư
        $uniqueInventories = collect();
        $seenNames = [];
        $seenCodes = [];
        foreach ($inventories as $inv) {
            if (!$inv->product) continue;
            $pCode = trim($inv->product->code);
            $pName = trim($inv->product->name);
            if (in_array($pCode, $seenCodes) || in_array($pName, $seenNames)) {
                continue;
            }
            $seenCodes[] = $pCode;
            $seenNames[] = $pName;
            $uniqueInventories->push($inv);
        }
        
        // Giới hạn 10 vật tư
        $inventories = $uniqueInventories->take(10);

        if ($inventories->isEmpty()) {
             session()->flash('error', "Không tìm thấy vật tư nào để kiểm kê.");
             return;
        }

        $code = 'KKD-' . date('Ymd') . '-' . str_pad(StockCount::count() + 1, 4, '0', STR_PAD_LEFT);

        $stockCount = StockCount::create([
            'code' => $code,
            'status' => 'pending',
            'type' => 'daily',
            'note' => 'Kiểm kê hàng ngày tự động',
            'created_by' => auth()->id(),
        ]);

        foreach ($inventories as $inv) {
            StockCountItem::create([
                'stock_count_id' => $stockCount->id,
                'product_id' => $inv->product_id,
                'system_quantity' => $inv->quantity,
                'actual_quantity' => null,
                'physical_quantity' => 0,
                'difference' => 0,
            ]);
        }

        $this->selectedStockCounts = [];
        $this->currentCountId = $stockCount->id;
        $this->activeTab = 'stocktake';
        session()->flash('success', "Đã tạo phiếu kiểm kê hàng ngày {$code} với " . $inventories->count() . " vật tư.");
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
        StockCount::findOrFail($id)->delete();
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
                StockCount::destroy($ids);
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
            $query = Inventory::with('product')
                ->join('products', 'inventories.product_id', '=', 'products.id')
                ->where('inventories.quantity', '>=', 0)
                ->orderBy('products.location')
                ->select('inventories.*');

            if ($this->locationFilter) {
                $query->where('inventories.warehouse_location', 'like', "%{$this->locationFilter}%");
            }

            $inventories = $query->get();

            // Lọc trùng lặp mã vật tư và tên vật tư
            $uniqueInventories = collect();
            $seenNames = [];
            $seenCodes = [];
            foreach ($inventories as $inv) {
                if (!$inv->product) continue;
                $pCode = trim($inv->product->code);
                $pName = trim($inv->product->name);
                if (in_array($pCode, $seenCodes) || in_array($pName, $seenNames)) {
                    continue;
                }
                $seenCodes[] = $pCode;
                $seenNames[] = $pName;
                $uniqueInventories->push($inv);
            }
            $inventories = $uniqueInventories;
            
            if ($inventories->isEmpty()) {
                session()->flash('error', 'Không có dữ liệu vật tư để xuất Excel.');
                return;
            }

            $data = $inventories->map(function($inv) {
                return [
                    'ma_san_pham' => $inv->product->code ?? 'N/A',
                    'ten_san_pham' => $inv->product->name ?? 'N/A',
                    'vi_tri' => $inv->product->location ?? $inv->warehouse_location ?? '-',
                    'ton_he_thong' => $inv->quantity,
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

            foreach ($inventories as $inv) {
                StockCountItem::create([
                    'stock_count_id' => $stockCount->id,
                    'product_id' => $inv->product_id,
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
}
