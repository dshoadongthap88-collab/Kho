<?php

namespace App\Livewire\Warehouse;

use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\Category;
use App\Models\Inventory;
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
            ->selectRaw('receiver_name, SUM(quantity) as total_qty')
            ->whereNotNull('receiver_name')
            ->where('receiver_name', '!=', '')
            ->whereBetween('stock_outs.created_at', [$this->dateFrom . ' 00:00:00', $this->dateTo . ' 23:59:59'])
            ->groupBy('receiver_name')
            ->orderByDesc('total_qty')
            ->take(10)
            ->get();

        return [
            'labels' => $data->pluck('receiver_name')->toArray(),
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

        foreach ($topProducts as $product) {
            $labels[] = $product->code;
            $stocks[] = (float)($product->inventory->quantity ?? 0);
            
            $stats = InventoryTransaction::selectRaw("
                SUM(CASE WHEN type = 'import' THEN quantity ELSE 0 END) as total_import,
                SUM(CASE WHEN type = 'export' THEN ABS(quantity) ELSE 0 END) as total_export
            ")
            ->where('product_id', $product->id)
            ->whereBetween('created_at', [$this->dateFrom . ' 00:00:00', $this->dateTo . ' 23:59:59'])
            ->first();

            $imports[] = (float)($stats->total_import ?? 0);
            $exports[] = (float)($stats->total_export ?? 0);
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
        $categories = Category::with(['products.inventory'])->get();
        
        $labels = [];
        $series = [];

        foreach ($categories as $category) {
            $totalStock = $category->products->sum(function($p) {
                return $p->inventory->quantity ?? 0;
            });

            if ($totalStock > 0) {
                $labels[] = $category->name;
                $series[] = (float)$totalStock;
            }
        }

        return [
            'labels' => $labels,
            'series' => $series
        ];
    }

    public function getParetoData()
    {
        $products = Product::with('inventory')
            ->get()
            ->sortByDesc(fn($p) => $p->inventory->quantity ?? 0)
            ->take(20);

        $totalInventory = Inventory::sum('quantity');
        if ($totalInventory == 0) $totalInventory = 1;

        $labels = [];
        $quantities = [];
        $cumulativePercentages = [];
        
        $currentSum = 0;
        foreach ($products as $product) {
            $qty = (float)($product->inventory->quantity ?? 0);
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

        foreach ($categories as $cat) {
            $products = Product::where('category_id', $cat->id)->with('inventory')->get();
            
            $normal = 0;
            $expiring = 0; 
            $expired = 0;
            $dead = 0; 

            foreach ($products as $p) {
                $qty = $p->inventory->quantity ?? 0;
                if ($qty <= 0) continue;

                if ($p->expiry_date) {
                    if ($p->expiry_date->isPast()) {
                        $expired++;
                    } elseif ($p->expiry_date->diffInDays(now()) <= 30) {
                        $expiring++;
                    } else {
                        $normal++;
                    }
                } else {
                    $normal++;
                }

                $lastTx = InventoryTransaction::where('product_id', $p->id)->latest()->first();
                if ($lastTx && $lastTx->created_at->diffInDays(now()) > 90) {
                    $dead++;
                }
            }

            $series[] = [
                'name' => $cat->name,
                'data' => [
                    ['x' => 'Bình thường', 'y' => $normal],
                    ['x' => 'Cận date', 'y' => $expiring],
                    ['x' => 'Đã hết hạn', 'y' => $expired],
                    ['x' => 'Tồn lâu (>90d)', 'y' => $dead],
                ]
            ];
        }

        return $series;
    }

    public function getWarnings()
    {
        $warnings = [];

        // 1. Cảnh báo tài sản tiêu thụ vật tư lớn nhất
        $topAsset = \App\Models\StockOut::join('stock_out_items', 'stock_outs.id', '=', 'stock_out_items.stock_out_id')
            ->selectRaw('asset_code, SUM(quantity) as total_qty')
            ->whereNotNull('asset_code')->where('asset_code', '!=', '')
            ->whereBetween('stock_outs.created_at', [$this->dateFrom . ' 00:00:00', $this->dateTo . ' 23:59:59'])
            ->groupBy('asset_code')->orderByDesc('total_qty')->first();
        
        if ($topAsset && $topAsset->total_qty > 0) {
            $warnings[] = [
                'type' => 'danger',
                'title' => 'Tài sản tiêu thụ cao nhất',
                'content' => "Mã tài sản <b>{$topAsset->asset_code}</b> đã tiêu thụ <b>" . number_format($topAsset->total_qty) . "</b> đơn vị vật tư trong kỳ. Cần kiểm tra định kỳ bảo trì.",
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
        $topReceiver = \App\Models\StockOut::join('stock_out_items', 'stock_outs.id', '=', 'stock_out_items.stock_out_id')
            ->selectRaw('receiver_name, SUM(quantity) as total_qty')
            ->whereNotNull('receiver_name')->where('receiver_name', '!=', '')
            ->whereBetween('stock_outs.created_at', [$this->dateFrom . ' 00:00:00', $this->dateTo . ' 23:59:59'])
            ->groupBy('receiver_name')->orderByDesc('total_qty')->first();

        if ($topReceiver) {
            $warnings[] = [
                'type' => 'info',
                'title' => 'Nhân viên lãnh hàng nhiều nhất',
                'content' => "Nhân viên <b>{$topReceiver->receiver_name}</b> đã lãnh tổng cộng <b>" . number_format($topReceiver->total_qty) . "</b> vật tư. Kiểm tra mục đích sử dụng nếu cần.",
                'icon' => '👤'
            ];
        }

        return $warnings;
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

        return view('livewire.warehouse.stock-report', [
            'summary' => $summary,
            'warnings' => $this->getWarnings(),
        ]);
    }
}
