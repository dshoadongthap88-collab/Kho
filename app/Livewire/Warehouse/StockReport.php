<?php

namespace App\Livewire\Warehouse;

use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\Category;
use App\Models\Inventory;
use App\Models\PurchasePlan;
use App\Models\Notification;
use App\Models\User;
use Livewire\Component;

class StockReport extends Component
{
    public $dateFrom = '';
    public $dateTo = '';

    // Chart data properties
    public $barData = ['series' => [], 'labels' => []];
    public $pieData = ['series' => [], 'labels' => []];
    public $paretoData = ['labels' => [], 'quantities' => [], 'percentages' => []];
    public $heatMapData = [];
    
    // New Stock-out Analytics
    public $receiverData = ['series' => [], 'labels' => []];
    public $assetData = ['series' => [], 'labels' => []];
    public $topExportData = ['series' => [], 'labels' => []];

    public function mount()
    {
        $this->dateFrom = now()->startOfMonth()->format('Y-m-d');
        $this->dateTo = now()->format('Y-m-d');
        $this->updateChartData();
    }

    public function updated($propertyName)
    {
        if (in_array($propertyName, ['dateFrom', 'dateTo'])) {
            $this->updateChartData();
        }
    }

    public function updateChartData()
    {
        $this->barData = $this->getBarChartData();
        $this->pieData = $this->getPieChartData();
        $this->paretoData = $this->getParetoData();
        $this->heatMapData = $this->getHeatMapData();
        
        // New Stock-out data
        $this->receiverData = $this->getReceiverData();
        $this->assetData = $this->getAssetData();
        $this->topExportData = $this->getTopExportData();
    }

    public function getReceiverData()
    {
        $data = \App\Models\StockOut::join('stock_out_items', 'stock_outs.id', '=', 'stock_out_items.stock_out_id')
            ->selectRaw('receiver_contact, SUM(quantity) as total_qty')
            ->whereNotNull('receiver_contact')
            ->where('receiver_contact', '!=', '')
            ->whereBetween('stock_outs.created_at', [$this->dateFrom . ' 00:00:00', $this->dateTo . ' 23:59:59'])
            ->groupBy('receiver_contact')
            ->orderByDesc('total_qty')
            ->take(10)
            ->get();

        return [
            'labels' => $data->pluck('receiver_contact')->toArray(),
            'series' => [['name' => 'Số lượng lãnh', 'data' => $data->pluck('total_qty')->map(fn($q) => (float)$q)->toArray()]]
        ];
    }

    public function getAssetData()
    {
        $data = \App\Models\StockOut::join('stock_out_items', 'stock_outs.id', '=', 'stock_out_items.stock_out_id')
            ->selectRaw('asset_code, SUM(quantity) as total_qty')
            ->whereNotNull('asset_code')
            ->where('asset_code', '!=', '')
            ->whereBetween('stock_outs.created_at', [$this->dateFrom . ' 00:00:00', $this->dateTo . ' 23:59:59'])
            ->groupBy('asset_code')
            ->orderByDesc('total_qty')
            ->take(10)
            ->get();

        return [
            'labels' => $data->pluck('asset_code')->toArray(),
            'series' => [['name' => 'Số lượng sử dụng', 'data' => $data->pluck('total_qty')->map(fn($q) => (float)$q)->toArray()]]
        ];
    }

    public function getTopExportData()
    {
        $data = \App\Models\StockOutItem::join('products', 'stock_out_items.product_id', '=', 'products.id')
            ->selectRaw('products.code, products.name, SUM(quantity) as total_qty')
            ->whereBetween('stock_out_items.created_at', [$this->dateFrom . ' 00:00:00', $this->dateTo . ' 23:59:59'])
            ->groupBy('products.id', 'products.code', 'products.name')
            ->orderByDesc('total_qty')
            ->take(10)
            ->get();

        return [
            'labels' => $data->map(fn($d) => $d->code)->toArray(),
            'series' => [['name' => 'Số lượng xuất', 'data' => $data->pluck('total_qty')->map(fn($q) => (float)$q)->toArray()]]
        ];
    }

    public function getBarChartData()
    {
        $topProducts = Product::with('inventory')
            ->whereHas('inventory', function($q) {
                $q->where('quantity', '>', 0);
            })
            ->take(10)
            ->get();

        $labels = [];
        $imports = [];
        $exports = [];
        $stocks = [];

        // Batch query: lấy stats cho tất cả products trong 1 query thay vì N queries trong foreach
        $productIds = $topProducts->pluck('id');
        $statsMap = InventoryTransaction::selectRaw("
                product_id,
                SUM(CASE WHEN type = 'import' THEN quantity ELSE 0 END) as total_import,
                SUM(CASE WHEN type = 'export' THEN ABS(quantity) ELSE 0 END) as total_export
            ")
            ->whereIn('product_id', $productIds)
            ->whereBetween('created_at', [$this->dateFrom . ' 00:00:00', $this->dateTo . ' 23:59:59'])
            ->groupBy('product_id')
            ->get()
            ->keyBy('product_id');

        foreach ($topProducts as $product) {
            $labels[] = $product->code;
            $stocks[] = (float)($product->inventory->quantity ?? 0);

            $stat = $statsMap->get($product->id);
            $imports[] = (float)($stat->total_import ?? 0);
            $exports[] = (float)($stat->total_export ?? 0);
        }

        return [
            'labels' => $labels,
            'series' => [
                ['name' => 'Nhập', 'data' => $imports],
                ['name' => 'Xuất', 'data' => $exports],
                ['name' => 'Tồn hiện tại', 'data' => $stocks],
            ]
        ];
    }

    public function getPieChartData()
    {
        // Tính thẳng trên DB thay vì load products.inventory vào PHP rồi sum
        $data = \Illuminate\Support\Facades\DB::table('categories')
            ->leftJoin('products', 'categories.id', '=', 'products.category_id')
            ->leftJoin('inventories', 'products.id', '=', 'inventories.product_id')
            ->where('products.status', 'active')
            ->selectRaw('categories.name, SUM(COALESCE(inventories.quantity, 0)) as total_stock')
            ->groupBy('categories.id', 'categories.name')
            ->having('total_stock', '>', 0)
            ->orderByDesc('total_stock')
            ->get();

        return [
            'labels' => $data->pluck('name')->toArray(),
            'series' => $data->pluck('total_stock')->map(fn($v) => (float)$v)->toArray(),
        ];
    }

    public function getParetoData()
    {
        // Dùng JOIN + ORDER BY trực tiếp trên DB thay vì load tất cả vào PHP rồi sort
        $products = Product::leftJoin('inventories', 'products.id', '=', 'inventories.product_id')
            ->where('products.status', 'active')
            ->selectRaw('products.code, COALESCE(inventories.quantity, 0) as qty')
            ->orderByDesc('qty')
            ->take(20)
            ->get();

        $totalInventory = (float)(\App\Models\Inventory::sum('quantity') ?: 1);

        $labels = [];
        $quantities = [];
        $cumulativePercentages = [];
        $currentSum = 0;

        foreach ($products as $product) {
            $qty = (float)$product->qty;
            if ($qty <= 0) continue;

            $labels[] = $product->code;
            $quantities[] = $qty;
            $currentSum += $qty;
            $cumulativePercentages[] = round(($currentSum / $totalInventory) * 100, 2);
        }

        return [
            'labels' => $labels,
            'quantities' => $quantities,
            'percentages' => $cumulativePercentages
        ];
    }

    public function getHeatMapData()
    {
        $categories = Category::all();
        $series = [];

        // Batch: lấy ngày giao dịch cuối cùng của từng product trong 1 query
        $lastTxDates = \Illuminate\Support\Facades\DB::table('inventory_transactions')
            ->selectRaw('product_id, MAX(created_at) as last_tx_at')
            ->groupBy('product_id')
            ->get()
            ->keyBy('product_id');

        // Load tất cả products + inventory trong 1 query thay vì N queries trong foreach
        $allProducts = Product::leftJoin('inventories', 'products.id', '=', 'inventories.product_id')
            ->where('products.status', 'active')
            ->selectRaw('products.id, products.category_id, products.expiry_date, COALESCE(inventories.quantity, 0) as qty')
            ->get()
            ->groupBy('category_id');

        $ninetyDaysAgo = now()->subDays(90);

        foreach ($categories as $cat) {
            $products = $allProducts->get($cat->id, collect());

            $normal = 0; $expiring = 0; $expired = 0; $dead = 0;

            foreach ($products as $p) {
                $qty = (float)$p->qty;
                if ($qty <= 0) continue;

                if ($p->expiry_date) {
                    $expiryDate = \Carbon\Carbon::parse($p->expiry_date);
                    if ($expiryDate->isPast()) {
                        $expired++;
                    } elseif ($expiryDate->diffInDays(now()) <= 30) {
                        $expiring++;
                    } else {
                        $normal++;
                    }
                } else {
                    $normal++;
                }

                // Dùng batch map thay vì query trong loop
                $lastTx = $lastTxDates->get($p->id);
                if ($lastTx && \Carbon\Carbon::parse($lastTx->last_tx_at)->lt($ninetyDaysAgo)) {
                    $dead++;
                }
            }

            $series[] = [
                'name' => $cat->name,
                'data' => [
                    ['x' => 'Bình thường',    'y' => $normal],
                    ['x' => 'Cận date',        'y' => $expiring],
                    ['x' => 'Đã hết hạn',      'y' => $expired],
                    ['x' => 'Tồn lâu (>90d)',  'y' => $dead],
                ]
            ];
        }

        return $series;
    }

    public function getWarnings()
    {
        $warnings = [];

        // 1. Cảnh báo tài sản tiêu thụ vật tư lớn nhất
        $topAssets = \App\Models\StockOut::join('stock_out_items', 'stock_outs.id', '=', 'stock_out_items.stock_out_id')
            ->selectRaw('asset_code, SUM(quantity) as total_qty')
            ->whereNotNull('asset_code')->where('asset_code', '!=', '')
            ->whereBetween('stock_outs.created_at', [$this->dateFrom . ' 00:00:00', $this->dateTo . ' 23:59:59'])
            ->groupBy('asset_code')->orderByDesc('total_qty')->take(10)->get();
        
        if ($topAssets->count() > 0) {
            $content = "<ul class='list-disc pl-4 space-y-1 mt-2'>";
            foreach ($topAssets as $index => $asset) {
                $content .= "<li>#" . ($index + 1) . " - Mã tài sản <b>{$asset->asset_code}</b>: <b>" . number_format($asset->total_qty) . "</b> đơn vị</li>";
            }
            $content .= "</ul>";

            $warnings[] = [
                'type' => 'danger',
                'title' => 'Top 10 Tài sản tiêu thụ vật tư',
                'content' => "Danh sách tài sản tiêu thụ vật tư nhiều nhất cần kiểm tra bảo trì:" . $content,
                'icon' => '⚠️'
            ];
        }

        // 2. Cảnh báo vật tư xuất kho nhiều nhưng tồn thấp (Sắp hết hàng)
        $topEx = \App\Models\StockOutItem::join('products', 'stock_out_items.product_id', '=', 'products.id')
            ->join('inventories', 'products.id', '=', 'inventories.product_id')
            ->selectRaw('products.name, products.code, SUM(stock_out_items.quantity) as total_qty, inventories.quantity as current_stock')
            ->whereBetween('stock_out_items.created_at', [$this->dateFrom . ' 00:00:00', $this->dateTo . ' 23:59:59'])
            ->groupBy('products.id', 'products.name', 'products.code', 'inventories.quantity')
            ->orderByDesc('total_qty')->take(5)->get();

        foreach ($topEx as $item) {
            if ($item->current_stock < ($item->total_qty / 2)) {
                $warnings[] = [
                    'type' => 'warning',
                    'title' => 'Vật tư sắp hết hàng (Fast-moving)',
                    'content' => "Sản phẩm <b>{$item->name}</b> ({$item->code}) có tốc độ xuất kho cao nhưng tồn hiện tại chỉ còn <b>" . number_format($item->current_stock) . "</b>. Đề xuất nhập thêm.",
                    'icon' => '📉'
                ];
            }
        }

        // 3. Cảnh báo nhân viên lãnh hàng nhiều nhất
        $topReceivers = \App\Models\StockOut::join('stock_out_items', 'stock_outs.id', '=', 'stock_out_items.stock_out_id')
            ->selectRaw('receiver_contact, SUM(quantity) as total_qty')
            ->whereNotNull('receiver_contact')->where('receiver_contact', '!=', '')
            ->whereBetween('stock_outs.created_at', [$this->dateFrom . ' 00:00:00', $this->dateTo . ' 23:59:59'])
            ->groupBy('receiver_contact')->orderByDesc('total_qty')->take(10)->get();

        if ($topReceivers->count() > 0) {
            $content = "<ul class='list-disc pl-4 space-y-1 mt-2'>";
            foreach ($topReceivers as $index => $receiver) {
                $content .= "<li>#" . ($index + 1) . " - <b>{$receiver->receiver_contact}</b>: <b>" . number_format($receiver->total_qty) . "</b> vật tư</li>";
            }
            $content .= "</ul>";

            $warnings[] = [
                'type' => 'info',
                'title' => 'Top 10 Nhân viên lãnh hàng',
                'content' => "Danh sách nhân viên nhận nhiều vật tư nhất trong kỳ:" . $content,
                'icon' => '👤'
            ];
        }

        return $warnings;
    }

    public function autoCreatePurchasePlan()
    {
        $predictiveStocks = \App\Models\StockOutItem::join('products', 'stock_out_items.product_id', '=', 'products.id')
            ->join('inventories', 'products.id', '=', 'inventories.product_id')
            ->selectRaw('products.id as product_id, products.name, products.code, SUM(stock_out_items.quantity) as total_out, inventories.quantity as current_stock')
            ->whereBetween('stock_out_items.created_at', [$this->dateFrom . ' 00:00:00', $this->dateTo . ' 23:59:59'])
            ->groupBy('products.id', 'products.name', 'products.code', 'inventories.quantity')
            ->havingRaw('SUM(stock_out_items.quantity) >= inventories.quantity')
            ->get();

        $count = 0;
        $itemsList = [];

        foreach ($predictiveStocks as $stock) {
            $existing = PurchasePlan::where('product_id', $stock->product_id)
                ->whereNotIn('status', ['completed'])
                ->first();

            if (!$existing) {
                // Đề xuất mua số lượng = Lượng xuất - Tồn (hoặc mặc định là 1 nếu bằng nhau)
                $diff = $stock->total_out - $stock->current_stock;
                $proposed = $diff > 0 ? $diff : 1;

                PurchasePlan::create([
                    'product_id' => $stock->product_id,
                    'proposed_quantity' => $proposed,
                    'status' => 'pending',
                    'notes' => 'Tự động đề xuất từ Báo Cáo Kho (Xuất ' . $stock->total_out . ' vượt Tồn ' . $stock->current_stock . ')',
                ]);
                $count++;
                $itemsList[] = "{$stock->name} (SL: $proposed)";
            }
        }

        if ($count > 0) {
            // Gửi thông báo tới HR (admin)
            $admins = User::where('role', 'admin')->get();
            $message = "Hệ thống vừa tự động lập kế hoạch mua hàng cho $count vật tư đang thiếu hụt trong kho: " . implode(', ', $itemsList);

            foreach ($admins as $admin) {
                Notification::create([
                    'user_id' => $admin->id,
                    'type' => 'system',
                    'title' => 'Tự động Đề Xuất Mua Hàng',
                    'message' => $message,
                    'is_read' => false,
                ]);
            }

            session()->flash('message', "Đã tự động lập kế hoạch mua hàng cho $count mã vật tư và gửi thông báo tới Ngôi nhà HR.");
        } else {
            session()->flash('error', 'Không có vật tư nào cần tạo đề xuất mua hàng (hoặc đã có kế hoạch đang chờ xử lý).');
        }
    }

    public function render()
    {
        $summary = InventoryTransaction::selectRaw("
                SUM(CASE WHEN type = 'import' THEN quantity ELSE 0 END) as total_import,
                SUM(CASE WHEN type = 'export' THEN ABS(quantity) ELSE 0 END) as total_export,
                SUM(CASE WHEN type = 'adjust' THEN quantity ELSE 0 END) as total_adjust
            ")
            ->whereBetween('created_at', [$this->dateFrom . ' 00:00:00', $this->dateTo . ' 23:59:59'])
            ->first();

        // 1. Tổng mã tài sản
        $totalAssets = \App\Models\StockOut::whereBetween('created_at', [$this->dateFrom . ' 00:00:00', $this->dateTo . ' 23:59:59'])
            ->whereNotNull('asset_code')->where('asset_code', '!=', '')
            ->distinct('asset_code')->count('asset_code');
            
        // 2. Tổng vật tư
        $totalMaterials = \App\Models\StockOutItem::join('stock_outs', 'stock_outs.id', '=', 'stock_out_items.stock_out_id')
            ->whereBetween('stock_outs.created_at', [$this->dateFrom . ' 00:00:00', $this->dateTo . ' 23:59:59'])
            ->sum('quantity');

        // 3. Dự đoán đặt hàng
        $predictiveStocks = \App\Models\StockOutItem::join('products', 'stock_out_items.product_id', '=', 'products.id')
            ->join('inventories', 'products.id', '=', 'inventories.product_id')
            ->selectRaw('products.name, products.code, SUM(stock_out_items.quantity) as total_out, inventories.quantity as current_stock')
            ->whereBetween('stock_out_items.created_at', [$this->dateFrom . ' 00:00:00', $this->dateTo . ' 23:59:59'])
            ->groupBy('products.id', 'products.name', 'products.code', 'inventories.quantity')
            ->havingRaw('SUM(stock_out_items.quantity) >= inventories.quantity')
            ->orderByDesc('total_out')->take(5)->get();

        // 4. Dead stock (Hơn 300 ngày)
        $deadStockLimit = now()->subDays(300);
        $deadStocks = \App\Models\Inventory::join('products', 'inventories.product_id', '=', 'products.id')
            ->select('products.name', 'products.code', 'products.unit', 'inventories.quantity', 'inventories.updated_at')
            ->where('inventories.quantity', '>', 0)
            ->where('inventories.updated_at', '<', $deadStockLimit)
            ->orderBy('inventories.quantity', 'desc')->get();

        // 5. Excess stock (Hàng thừa)
        $excessStocks = \App\Models\Inventory::join('products', 'inventories.product_id', '=', 'products.id')
            ->select('products.name', 'products.code', 'products.unit', 'products.max_stock', 'inventories.quantity')
            ->where('products.max_stock', '>', 0)
            ->whereRaw('inventories.quantity > products.max_stock')
            ->orderByRaw('(inventories.quantity - products.max_stock) DESC')
            ->get();

        return view('livewire.warehouse.stock-report', [
            'summary' => $summary,
            'warnings' => $this->getWarnings(),
            'totalAssets' => $totalAssets,
            'totalMaterials' => $totalMaterials,
            'predictiveStocks' => $predictiveStocks,
            'deadStocks' => $deadStocks,
            'excessStocks' => $excessStocks,
        ]);
    }
}
