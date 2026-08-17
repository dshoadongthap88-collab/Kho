<?php

namespace App\Livewire\Warehouse\Reports;

use Livewire\Component;
use App\Models\Inventory;
use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\Category;
use App\Models\StockOut;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportDashboard extends Component
{
    public $houseId;
    public $selectedProducts = []; // Cho khối dead stock

    public function mount()
    {
        $this->houseId = request()->route('house_id') ?? 1; // Mặc định nếu không có context
    }

    // 1. Biểu đồ Bar Chart: Nhập - Xuất - Tồn (Top 10 sản phẩm biến động nhiều nhất)
    public function getInventoryInOutStats()
    {
        // Lấy top 10 sản phẩm có giao dịch nhiều nhất trong tháng này
        $currentMonth = Carbon::now()->startOfMonth();
        
        $topProducts = InventoryTransaction::select('product_id', DB::raw('COUNT(*) as tx_count'))
            ->where('house_id', $this->houseId)
            ->where('created_at', '>=', $currentMonth)
            ->groupBy('product_id')
            ->orderByDesc('tx_count')
            ->limit(10)
            ->pluck('product_id');

        if ($topProducts->isEmpty()) {
            // Nếu không có giao dịch tháng này, lấy top 10 sản phẩm bất kỳ
            $topProducts = Product::limit(10)->pluck('id');
        }

        $stats = [];
        $labels = [];
        $dataNhập = [];
        $dataXuất = [];
        $dataTồn = [];

        foreach ($topProducts as $productId) {
            $product = Product::find($productId);
            if (!$product) continue;

            $labels[] = $product->code ?? $product->name;
            
            // Tính tổng nhập, xuất
            $nhap = InventoryTransaction::where('house_id', $this->houseId)
                ->where('product_id', $productId)
                ->where('type', 'import')
                ->sum('quantity');

            $xuat = InventoryTransaction::where('house_id', $this->houseId)
                ->where('product_id', $productId)
                ->where('type', 'export')
                ->sum('quantity');

            $xuat = abs($xuat);

            $ton = Inventory::where('house_id', $this->houseId)
                ->where('product_id', $productId)
                ->sum('quantity');

            $dataNhập[] = $nhap;
            $dataXuất[] = $xuat;
            $dataTồn[] = $ton;
        }

        return [
            'labels' => $labels,
            'series' => [
                ['name' => 'Tổng Nhập', 'data' => $dataNhập],
                ['name' => 'Tổng Xuất', 'data' => $dataXuất],
                ['name' => 'Tồn Kho', 'data' => $dataTồn],
            ]
        ];
    }

    // 2. Biểu đồ Pie Chart: Phân tích tồn kho theo danh mục
    public function getCategoryDistribution()
    {
        $categories = Category::all();
        $labels = [];
        $series = [];

        foreach ($categories as $category) {
            $productIds = $category->products()->pluck('id');
            if ($productIds->isEmpty()) continue;

            $totalQuantity = Inventory::where('house_id', $this->houseId)
                ->whereIn('product_id', $productIds)
                ->sum('quantity');

            if ($totalQuantity > 0) {
                $labels[] = $category->name;
                $series[] = (int) $totalQuantity;
            }
        }

        return [
            'labels' => $labels,
            'series' => $series
        ];
    }

    // 3. Pareto Chart Data (Top 20% mặt hàng chiếm 80% luân chuyển)
    // Sẽ kết hợp vào Bar chart hoặc tách riêng. Ở đây viết data.
    public function getParetoData()
    {
        $transactions = InventoryTransaction::select('product_id', DB::raw('SUM(quantity) as total_qty'))
            ->where('house_id', $this->houseId)
            ->where('type', 'export')
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->limit(15)
            ->get();

        $labels = [];
        $qtyData = [];
        
        foreach ($transactions as $tx) {
            $labels[] = $tx->product->name ?? 'Unknown';
            $qtyData[] = abs($tx->total_qty);
        }

        return [
            'labels' => $labels,
            'series' => [
                ['name' => 'Số lượng giao dịch', 'type' => 'column', 'data' => $qtyData],
            ]
        ];
    }

    // 4. Thống kê Nhân viên nhận hàng nhiều nhất
    public function getTopReceivers()
    {
        return StockOut::select('receiver_contact', DB::raw('COUNT(*) as total_orders'))
            ->where('house_id', $this->houseId)
            ->whereNotNull('receiver_contact')
            ->where('receiver_contact', '!=', '')
            ->groupBy('receiver_contact')
            ->orderByDesc('total_orders')
            ->limit(5)
            ->get();
    }

    // 5. Khối Cảnh báo không sử dụng > 300 ngày (Dead Stock)
    public function getDeadStock()
    {
        $thresholdDate = Carbon::now()->subDays(300);

        // Lấy các inventory mà transaction xuất gần nhất đã quá 300 ngày hoặc chưa bao giờ xuất nhưng nhập > 300 ngày
        // Để đơn giản: Lấy product_id trong inventory, check transaction cuối cùng của product đó.
        
        $inventoryProducts = Inventory::where('house_id', $this->houseId)
            ->where('quantity', '>', 0)
            ->pluck('product_id')
            ->unique();

        $deadStocks = [];

        foreach ($inventoryProducts as $pId) {
            $lastTx = InventoryTransaction::where('house_id', $this->houseId)
                ->where('product_id', $pId)
                ->latest('created_at')
                ->first();

            if ($lastTx && $lastTx->created_at < $thresholdDate) {
                $inv = Inventory::where('house_id', $this->houseId)->where('product_id', $pId)->first();
                $deadStocks[] = [
                    'product_id' => $pId,
                    'product_code' => $inv->product->code ?? '',
                    'product_name' => $inv->product->name ?? '',
                    'unit' => $inv->product->unit ?? '',
                    'quantity' => $inv->quantity,
                    'supplier' => $inv->product->supplier->name ?? 'N/A',
                    'last_transaction_date' => $lastTx->created_at->format('Y-m-d'),
                    'days_inactive' => $lastTx->created_at->diffInDays(Carbon::now())
                ];
            }
        }

        // Sắp xếp theo số ngày ko HĐ giảm dần
        usort($deadStocks, function($a, $b) {
            return $b['days_inactive'] <=> $a['days_inactive'];
        });

        return $deadStocks;
    }

    // Hành động khi tick chọn in
    public function toggleSelectProduct($productId)
    {
        if (in_array($productId, $this->selectedProducts)) {
            $this->selectedProducts = array_diff($this->selectedProducts, [$productId]);
        } else {
            $this->selectedProducts[] = $productId;
        }
    }

    public function selectAllDeadStocks($productIds)
    {
        $this->selectedProducts = $productIds;
    }

    public function printDeadStocks()
    {
        if (empty($this->selectedProducts)) {
            session()->flash('error', 'Vui lòng chọn ít nhất một vật tư để in báo cáo.');
            return;
        }

        $ids = implode(',', $this->selectedProducts);
        $this->dispatch('open-print-tab', url: route('warehouse.prints.dead-stock') . '?ids=' . $ids);
    }

    public function render()
    {
        return view('livewire.warehouse.reports.report-dashboard', [
            'barChartData' => $this->getInventoryInOutStats(),
            'pieChartData' => $this->getCategoryDistribution(),
            'paretoData' => $this->getParetoData(),
            'topReceivers' => $this->getTopReceivers(),
            'deadStocks' => $this->getDeadStock(),
        ])->layout('components.warehouse-layout', ['title' => 'Dashboard Tổng Hợp Kho']);
    }
}
