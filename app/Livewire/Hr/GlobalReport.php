<?php

namespace App\Livewire\Hr;

use Livewire\Component;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Models\StockOut;
use App\Models\StockOutItem;

class GlobalReport extends Component
{
    public $activeTab = 'overview';
    public $selectedProject = '';
    public $startDate = '';
    public $endDate = '';
    
    public $warningProject = ''; // Thêm filter cho tab cảnh báo

    public function setTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function render()
    {
        $projects = \App\Models\House::where('status', 'active')
            ->where('id', '!=', 5) // Exclude HR
            ->get();
        $totalUsers = User::count();
        
        $now = now();
        $thirtyDaysAgo = now()->subDays(30)->startOfDay();

        // 1. Tổng số đơn xuất kho / dự án
        $ordersPerProject = StockOut::withoutGlobalScope('house')
            ->where('house_id', '!=', 5)
            ->select('house_id', DB::raw('count(*) as total_orders'))
            ->groupBy('house_id')
            ->pluck('total_orders', 'house_id')
            ->toArray();

        // 2. Tổng mã vật tư xuất / dự án (Tổng số lượng)
        $itemsPerProject = DB::table('stock_out_items')
            ->join('stock_outs', 'stock_out_items.stock_out_id', '=', 'stock_outs.id')
            ->where('stock_outs.house_id', '!=', 5)
            ->select('stock_outs.house_id', DB::raw('SUM(stock_out_items.quantity) as total_items'))
            ->groupBy('stock_outs.house_id')
            ->pluck('total_items', 'house_id')
            ->toArray();

        // 3. Tổng số đơn xuất kho tất cả dự án / ngày (30 ngày qua)
        $ordersPerDay = StockOut::withoutGlobalScope('house')
            ->where('house_id', '!=', 5)
            ->where('created_at', '>=', $thirtyDaysAgo)
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as total_orders'))
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date', 'asc')
            ->get();

        // Chuẩn bị dữ liệu cho biểu đồ đường (Time-series)
        $dates = [];
        $orderCounts = [];
        
        // Lấp đầy các ngày trống
        $currentDate = clone $thirtyDaysAgo;
        $orderMap = $ordersPerDay->pluck('total_orders', 'date')->toArray();
        
        while ($currentDate <= $now) {
            $dateStr = $currentDate->format('Y-m-d');
            $dates[] = $currentDate->format('d/m');
            $orderCounts[] = $orderMap[$dateStr] ?? 0;
            $currentDate->addDay();
        }

        // 4. Tổng mã vật tư xuất kho tất cả dự án
        $totalItemsAllProjects = array_sum($itemsPerProject);
        $totalOrdersAllProjects = array_sum($ordersPerProject);

        // Chuẩn bị dữ liệu cho biểu đồ cột và tròn
        $projectNames = [];
        $projectOrders = [];
        $projectItems = [];

        foreach ($projects as $project) {
            $projectNames[] = $project->name;
            $projectOrders[] = $ordersPerProject[$project->id] ?? 0;
            $projectItems[] = (int)($itemsPerProject[$project->id] ?? 0);
        }

        // --- Tab 2: Cảnh báo tồn kho ---
        $lowStockProducts = [];
        $highStockProducts = [];
        if ($this->activeTab === 'inventory_warnings') {
            $allProducts = \App\Models\Product::withoutGlobalScope('house')->select('id', 'code', 'name', 'unit', 'min_stock', 'max_stock')->get()->keyBy('id');
            
            $inventoryQuery = DB::table('inventories')
                ->where('inventories.house_id', '!=', 5)
                ->join('houses', 'inventories.house_id', '=', 'houses.id')
                ->select('inventories.product_id', 'inventories.house_id', 'houses.name as project_name', DB::raw('SUM(inventories.quantity) as total_qty'))
                ->groupBy('inventories.product_id', 'inventories.house_id', 'houses.name');
                
            if ($this->warningProject) {
                $inventoryQuery->where('inventories.house_id', $this->warningProject);
            }
            
            $inventoryList = $inventoryQuery->get();

            foreach ($inventoryList as $inv) {
                $product = $allProducts->get($inv->product_id);
                if (!$product) continue;
                
                $qty = $inv->total_qty;
                if ($product->min_stock > 0 && $qty < $product->min_stock) {
                    $lowStockProducts[] = (object)[
                        'code' => $product->code,
                        'name' => $product->name,
                        'unit' => $product->unit,
                        'min_stock' => $product->min_stock,
                        'quantity' => $qty,
                        'project_name' => $inv->project_name
                    ];
                }
                if ($product->max_stock > 0 && $qty > $product->max_stock) {
                    $highStockProducts[] = (object)[
                        'code' => $product->code,
                        'name' => $product->name,
                        'unit' => $product->unit,
                        'max_stock' => $product->max_stock,
                        'quantity' => $qty,
                        'project_name' => $inv->project_name
                    ];
                }
            }
        }

        // --- Tab 3: Báo cáo chi tiết xuất kho ---
        $stockOutDetails = [];
        if ($this->activeTab === 'stock_out_details') {
            $query = StockOut::withoutGlobalScope('house')
                ->where('stock_outs.house_id', '!=', 5)
                ->join('stock_out_items', 'stock_outs.id', '=', 'stock_out_items.stock_out_id')
                ->join('products', 'stock_out_items.product_id', '=', 'products.id')
                ->leftJoin('houses', 'stock_outs.house_id', '=', 'houses.id')
                ->select(
                    'stock_outs.created_at as date',
                    'houses.name as project_name',
                    'stock_outs.asset_code',
                    'stock_outs.repair_staff',
                    'products.code as product_code',
                    'products.name as product_name',
                    'stock_out_items.quantity'
                );

            if ($this->selectedProject) {
                $query->where('stock_outs.house_id', $this->selectedProject);
            }
            if ($this->startDate) {
                $query->whereDate('stock_outs.created_at', '>=', $this->startDate);
            }
            if ($this->endDate) {
                $query->whereDate('stock_outs.created_at', '<=', $this->endDate);
            }

            $stockOutDetails = $query->orderBy('stock_outs.created_at', 'desc')->limit(500)->get();
        }

        return view('livewire.hr.global-report', [
            'projects' => $projects,
            'totalUsers' => $totalUsers,
            'projectNames' => $projectNames,
            'projectOrders' => $projectOrders,
            'projectItems' => $projectItems,
            'dates' => $dates,
            'orderCounts' => $orderCounts,
            'totalItemsAllProjects' => $totalItemsAllProjects,
            'totalOrdersAllProjects' => $totalOrdersAllProjects,
            'lowStockProducts' => $lowStockProducts,
            'highStockProducts' => $highStockProducts,
            'stockOutDetails' => $stockOutDetails,
            'totalOrdersAllProjects' => $totalOrdersAllProjects,
        ])->layout('components.warehouse-layout');
    }
}
