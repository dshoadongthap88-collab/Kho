<?php

namespace App\Livewire\Warehouse;

use App\Models\InventoryTransaction;
use Livewire\Component;
use Livewire\WithPagination;
use App\Exports\TransactionExport;
use Maatwebsite\Excel\Facades\Excel;

class Reports extends Component
{
    use WithPagination;

    public $dateFrom = '';
    public $dateTo = '';
    public $filterType = '';
    public $filterProduct = '';

    // Chart data properties
    public $barData = ['series' => [], 'labels' => []];
    public $pieData = ['series' => [], 'labels' => []];
    public $paretoData = ['labels' => [], 'quantities' => [], 'percentages' => []];
    public $heatMapData = [];

    public function mount()
    {
        $this->dateFrom = now()->startOfMonth()->format('Y-m-d');
        $this->dateTo = now()->format('Y-m-d');
    }

    public function getBarChartData()
    {
        // Lấy top 10 sản phẩm có tồn kho cao nhất hoặc giao dịch nhiều nhất
        $topProducts = \App\Models\Product::with('inventory')
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
        $categories = \App\Models\Category::with(['products.inventory'])->get();
        
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
        $products = \App\Models\Product::with('inventory')
            ->get()
            ->sortByDesc(fn($p) => $p->inventory->quantity ?? 0)
            ->take(20);

        $totalInventory = \App\Models\Inventory::sum('quantity');
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
        // Phân loại theo "Sức khỏe" mặt hàng
        // Rows: Nhóm hàng (Category)
        // Cols: Trạng thái (Bình thường, Cận date, Hết hạn, Tồn lâu)
        $categories = \App\Models\Category::all();
        $series = [];

        foreach ($categories as $cat) {
            $products = \App\Models\Product::where('category_id', $cat->id)->with('inventory')->get();
            
            $normal = 0;
            $expiring = 0; // < 30 ngày
            $expired = 0;
            $dead = 0; // Không có giao dịch trong 90 ngày

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

                // Kiểm tra hàng tồn lâu (giả định dựa trên giao dịch cuối)
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

    public function exportExcel()
    {
        $query = InventoryTransaction::with(['product', 'creator'])
            ->whereBetween('created_at', [$this->dateFrom . ' 00:00:00', $this->dateTo . ' 23:59:59']);

        if ($this->filterType) {
            $query->where('type', $this->filterType);
        }

        if ($this->filterProduct) {
            $query->whereHas('product', function ($q) {
                $q->where('name', 'like', "%{$this->filterProduct}%")
                  ->orWhere('code', 'like', "%{$this->filterProduct}%");
            });
        }

        $data = $query->orderBy('created_at', 'desc')->get();
        return Excel::download(new TransactionExport($data), 'bao_cao_giao_dich_kho_' . now()->format('Ymd_His') . '.xlsx');
    }

    public function render()
    {
        $query = InventoryTransaction::with(['product', 'creator', 'product.inventory'])
            ->whereBetween('created_at', [$this->dateFrom . ' 00:00:00', $this->dateTo . ' 23:59:59']);

        if ($this->filterType) {
            $query->where('type', $this->filterType);
        }

        if ($this->filterProduct) {
            $query->whereHas('product', function ($q) {
                $q->where('name', 'like', "%{$this->filterProduct}%")
                  ->orWhere('code', 'like', "%{$this->filterProduct}%");
            });
        }

        $transactions = $query->orderBy('created_at', 'desc')->paginate(10); // Giảm bớt số lượng trang để ưu tiên biểu đồ

        // Tổng hợp nhập/xuất trong khoảng thời gian
        $summary = InventoryTransaction::selectRaw("
                SUM(CASE WHEN type = 'import' THEN quantity ELSE 0 END) as total_import,
                SUM(CASE WHEN type = 'export' THEN ABS(quantity) ELSE 0 END) as total_export,
                SUM(CASE WHEN type = 'adjust' THEN quantity ELSE 0 END) as total_adjust
            ")
            ->whereBetween('created_at', [$this->dateFrom . ' 00:00:00', $this->dateTo . ' 23:59:59'])
            ->first();

        // Cập nhật dữ liệu biểu đồ vào các thuộc tính public
        $this->barData = $this->getBarChartData();
        $this->pieData = $this->getPieChartData();
        $this->paretoData = $this->getParetoData();
        $this->heatMapData = $this->getHeatMapData();

        return view('livewire.warehouse.reports', [
            'transactions' => $transactions,
            'summary' => $summary,
        ]);
    }
}
