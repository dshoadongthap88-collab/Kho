<?php

namespace App\Livewire\Warehouse;

use App\Models\Inventory;
use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\StockCount;
use App\Models\StockCountItem;
use App\Services\InventoryService;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;



class StockCountForm extends Component
{
    use WithPagination;

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

    protected $listeners = [];

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

        // Thêm tất cả items vào phiếu
        $inventories = Inventory::with('product')->where('quantity', '>=', 0)->get();

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
            ->unique();

        // 2. Lấy 10 sản phẩm theo vị trí (proximity)
        $inventories = Inventory::with('product')
            ->join('products', 'inventories.product_id', '=', 'products.id')
            ->whereNotIn('inventories.product_id', $recentProductIds)
            ->where('inventories.quantity', '>=', 0)
            ->orderBy('products.location')
            ->limit(10)
            ->select('inventories.*')
            ->get();

        if ($inventories->isEmpty()) {
            // Nếu tất cả đã được kiểm trong 7 ngày, lấy 10 cái bất kỳ
             $inventories = Inventory::with('product')
                ->join('products', 'inventories.product_id', '=', 'products.id')
                ->where('inventories.quantity', '>=', 0)
                ->orderBy('products.location')
                ->limit(10)
                ->select('inventories.*')
                ->get();
        }

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

        $this->currentCountId = $stockCount->id;
        $this->activeTab = 'stocktake';
        session()->flash('success', "Đã tạo phiếu kiểm kê hàng ngày {$code} với 10 vật tư.");
    }



    public function updateActualQty($itemId, $actualQty)
    {
        $item = StockCountItem::find($itemId);
        if (!$item) return;

        $actual = (float) $actualQty;
        $item->update([
            'actual_quantity' => $actual,
            'physical_quantity' => $actual,
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
                if ($item->actual_quantity !== null && $item->difference != 0) {
                    $service->adjustQuantity(
                        $item->product_id,
                        (int) $item->actual_quantity,
                        "Điều chỉnh từ phiếu kiểm kê {$stockCount->code}"
                    );
                    $adjustedCount++;
                }
            }

            $stockCount->update(['status' => 'completed']);
        });

        $this->currentCountId = null;
        session()->flash('success', "Đã xác nhận kiểm kê và điều chỉnh {$adjustedCount} sản phẩm.");
    }

    public function cancelStockCount($stockCountId)
    {
        StockCount::findOrFail($stockCountId)->update(['status' => 'cancelled']);
        if ($this->currentCountId == $stockCountId) {
            $this->currentCountId = null;
        }
        session()->flash('info', 'Đã hủy phiếu kiểm kê.');
    }

    public function editStockCount($id)
    {
        $this->currentCountId = $id;
        $this->activeTab = 'stocktake';
    }

    public function deleteStockCount($id)
    {
        StockCount::findOrFail($id)->delete();
        session()->flash('info', 'Đã xóa phiếu kiểm kê.');
    }

    public function bulkDelete()
    {
        if (empty($this->selectedStockCounts)) {
            session()->flash('error', 'Vui lòng chọn ít nhất một phiếu để xóa.');
            return;
        }

        // Đảm bảo các ID là số nguyên
        $ids = collect($this->selectedStockCounts)->map(fn($id) => (int)$id)->toArray();
        
        StockCount::whereIn('id', $ids)->delete();
        
        $this->selectedStockCounts = [];
        session()->flash('success', 'Đã xóa các phiếu được chọn.');
    }

    public function bulkPrint()
    {
        if (empty($this->selectedStockCounts)) {
            session()->flash('error', 'Vui lòng chọn ít nhất một phiếu để in.');
            return;
        }

        session()->flash('info', 'Tính năng in hàng loạt ' . count($this->selectedStockCounts) . ' phiếu đang được chuẩn bị.');
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

        return view('livewire.warehouse.stock-count-form', [
            'stockCounts' => $stockCounts,
            'currentCount' => $currentCount,
        ]);
    }
}
