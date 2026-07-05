<?php

namespace App\Livewire\Hr;

use Livewire\Component;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class GlobalReport extends Component
{
    public function render()
    {
        $projects = \App\Models\House::where('status', 'active')
            ->where('name', 'not like', '%HR%')
            ->get();
        $totalUsers = User::count();
        
        $stats = [];
        $now = now();
        $days300Ago = now()->subDays(300);

        foreach ($projects as $project) {
            $houseId = $project->id;
            $inventoryCount = \App\Models\Inventory::withoutGlobalScope('house')->where('house_id', $houseId)->sum('quantity');
            $stockInCount = \App\Models\StockIn::withoutGlobalScope('house')->where('house_id', $houseId)->count();
            
            // 1. Top 5 mã tài sản sử dụng nhiều nhất (theo số lần xuất)
            $topAssets = \App\Models\StockOut::withoutGlobalScope('house')
                ->where('house_id', $houseId)
                ->whereNotNull('asset_code')
                ->where('asset_code', '!=', '')
                ->select('asset_code', DB::raw('count(*) as usage_count'))
                ->groupBy('asset_code')
                ->orderByDesc('usage_count')
                ->limit(5)
                ->get();

            // 2. Top 5 mã vật tư sử dụng nhiều nhất (theo tổng số lượng xuất)
            $topMaterials = DB::table('stock_out_items')
                ->join('stock_outs', 'stock_out_items.stock_out_id', '=', 'stock_outs.id')
                ->join('products', 'stock_out_items.product_id', '=', 'products.id')
                ->where('stock_outs.house_id', $houseId)
                ->select('products.code', 'products.name', DB::raw('SUM(stock_out_items.quantity) as total_used'))
                ->groupBy('products.id', 'products.code', 'products.name')
                ->orderByDesc('total_used')
                ->limit(5)
                ->get();

            // 3. Bao nhiêu mã vật tư sắp hết tồn kho
            $lowStockCount = \App\Models\Inventory::withoutGlobalScope('house')
                ->join('products', 'inventories.product_id', '=', 'products.id')
                ->where('inventories.house_id', $houseId)
                ->where('inventories.quantity', '>', 0)
                ->whereRaw('inventories.quantity <= products.min_stock')
                ->count();

            // 4. Mã vật tư không sử dụng trên 300 ngày kể từ ngày nhập kho
            // Tìm trong inventories có tồn > 0 nhưng giao dịch cuối (max transaction_date) < 300 ngày trước
            $obsoleteStockCount = DB::table('inventories')
                ->join('inventory_transactions', 'inventories.product_id', '=', 'inventory_transactions.product_id')
                ->where('inventories.house_id', $houseId)
                ->where('inventory_transactions.house_id', $houseId)
                ->where('inventories.quantity', '>', 0)
                ->select('inventories.product_id')
                ->groupBy('inventories.product_id')
                ->havingRaw('MAX(inventory_transactions.transaction_date) < ?', [$days300Ago])
                ->get()
                ->count();

            $stats[$houseId] = [
                'users' => User::whereJsonContains('allowed_houses', (int)$houseId)->orWhereJsonContains('allowed_houses', (string)$houseId)->count(),
                'stock_value' => $inventoryCount,
                'active_orders' => $stockInCount,
                'top_assets' => $topAssets,
                'top_materials' => $topMaterials,
                'low_stock_count' => $lowStockCount,
                'obsolete_stock_count' => $obsoleteStockCount,
            ];
        }

        return view('livewire.hr.global-report', compact('projects', 'totalUsers', 'stats'))
            ->layout('layouts.app');
    }
}
