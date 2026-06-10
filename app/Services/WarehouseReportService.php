<?php

namespace App\Services;

use App\Models\Warehouse;
use App\Models\Inventory;
use App\Models\InventoryTransaction;
use App\Models\StockIn;
use App\Models\StockOut;
use App\Models\StockTransfer;
use App\Models\StockCount;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class WarehouseReportService
{
    public function getDashboardSummary(): array
    {
        $totalWarehouses = Warehouse::count();
        $totalProducts = Product::where('status', 'active')->count();
        $totalStockValue = Inventory::join('products', 'inventories.product_id', '=', 'products.id')
            ->sum(DB::raw('inventories.quantity * products.price'));

        $today = Carbon::today();
        $totalImportToday = StockIn::whereDate('created_at', $today)->count();
        $totalExportToday = StockOut::whereDate('created_at', $today)->count();
        $totalTransferToday = StockTransfer::whereDate('created_at', $today)->count();

        return [
            'total_warehouses' => $totalWarehouses,
            'total_products' => $totalProducts,
            'total_stock_value' => round($totalStockValue, 2),
            'total_import_today' => $totalImportToday,
            'total_export_today' => $totalExportToday,
            'total_transfer_today' => $totalTransferToday,
            'pending_transfers' => StockTransfer::where('status', 'pending')->count(),
            'pending_stock_counts' => StockCount::where('status', 'in_progress')->count(),
        ];
    }

    public function getAllWarehousesReport(array $filters = []): array
    {
        $query = Warehouse::query();

        if (isset($filters['status']) && $filters['status'] !== 'all') {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%");
            });
        }

        $warehouses = $query->orderBy('name')->get();
        $report = [];
        foreach ($warehouses as $wh) {
            $inventoryQuery = Inventory::join('products', 'inventories.product_id', '=', 'products.id')
                ->where(function ($q) use ($wh) {
                    $q->where('inventories.warehouse_location', $wh->name)
                        ->orWhere('products.location', $wh->name);
                });

            $report[] = [
                'id' => $wh->id,
                'code' => $wh->code,
                'name' => $wh->name,
                'address' => $wh->address,
                'manager_name' => $wh->manager_name,
                'phone' => $wh->phone,
                'status' => $wh->status,
                'total_products' => (clone $inventoryQuery)->count(),
                'total_quantity' => round((clone $inventoryQuery)->sum('inventories.quantity'), 2),
                'total_value' => round((clone $inventoryQuery)->sum(DB::raw('inventories.quantity * products.price')), 2),
            ];
        }
        return $report;
    }

    public function getInventoryByWarehouse(int $warehouseId, array $filters = []): \Illuminate\Pagination\LengthAwarePaginator
    {
        $warehouse = Warehouse::findOrFail($warehouseId);
        $query = Inventory::join('products', 'inventories.product_id', '=', 'products.id')
            ->where(function ($q) use ($warehouse) {
                $q->where('inventories.warehouse_location', $warehouse->name)
                    ->orWhere('products.location', $warehouse->name);
            })
            ->select(
                'inventories.id',
                'products.code as product_code',
                'products.name as product_name',
                'products.unit',
                'inventories.quantity',
                'products.min_stock',
                'inventories.warehouse_location',
                DB::raw('inventories.quantity * products.price as total_value')
            );

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('products.code', 'like', "%{$filters['search']}%")
                    ->orWhere('products.name', 'like', "%{$filters['search']}%");
            });
        }

        if (!empty($filters['status'])) {
            switch ($filters['status']) {
                case 'low_stock':
                    $query->whereColumn('inventories.quantity', '<=', 'products.min_stock');
                    break;
                case 'out_of_stock':
                    $query->where('inventories.quantity', '<=', 0);
                    break;
                case 'over_stock':
                    $query->whereColumn('inventories.quantity', '>', 'products.min_stock');
                    break;
            }
        }
        return $query->paginate(20);
    }

    public function getStockMovementsReport(array $filters = []): \Illuminate\Pagination\LengthAwarePaginator
    {
        $query = InventoryTransaction::query()
            ->join('products', 'inventory_transactions.product_id', '=', 'products.id')
            ->select(
                'inventory_transactions.id',
                'inventory_transactions.transaction_date',
                'inventory_transactions.type',
                'inventory_transactions.quantity',
                'inventory_transactions.note',
                'inventory_transactions.warehouse_location',
                'products.code as product_code',
                'products.name as product_name',
                'products.unit',
                DB::raw('users.name as creator_name')
            )
            ->leftJoin('users', 'inventory_transactions.created_by', '=', 'users.id')
            ->orderBy('inventory_transactions.transaction_date', 'desc');

        if (!empty($filters['type'])) {
            $query->where('inventory_transactions.type', $filters['type']);
        }
        if (!empty($filters['product_id'])) {
            $query->where('inventory_transactions.product_id', $filters['product_id']);
        }
        if (!empty($filters['warehouse_location'])) {
            $query->where('inventory_transactions.warehouse_location', $filters['warehouse_location']);
        }
        if (!empty($filters['date_from'])) {
            $query->whereDate('inventory_transactions.transaction_date', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->whereDate('inventory_transactions.transaction_date', '<=', $filters['date_to']);
        }
        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('products.code', 'like', "%{$filters['search']}%")
                    ->orWhere('products.name', 'like', "%{$filters['search']}%");
            });
        }
        return $query->paginate(30);
    }

    public function getMonthlyStats(): \Illuminate\Support\Collection
    {
        $startOfYear = Carbon::now()->startOfYear();
        $imports = InventoryTransaction::where('type', 'import')
            ->whereDate('transaction_date', '>=', $startOfYear)
            ->select(DB::raw('MONTH(transaction_date) as month'), DB::raw('SUM(quantity) as total_quantity'))
            ->groupBy('month')->get()->keyBy('month');

        $exports = InventoryTransaction::where('type', 'export')
            ->whereDate('transaction_date', '>=', $startOfYear)
            ->select(DB::raw('MONTH(transaction_date) as month'), DB::raw('SUM(ABS(quantity)) as total_quantity'))
            ->groupBy('month')->get()->keyBy('month');

        return collect(range(1, 12))->map(function ($month) use ($imports, $exports) {
            return [
                'month' => $month,
                'month_name' => Carbon::create(null, $month, 1)->format('F'),
                'import_quantity' => $imports->get($month)?->total_quantity ?? 0,
                'export_quantity' => $exports->get($month)?->total_quantity ?? 0,
            ];
        });
    }
}
