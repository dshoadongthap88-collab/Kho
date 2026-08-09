<?php

use Illuminate\Support\Facades\Route;

Route::prefix('warehouse')->name('warehouse.')->group(function () {
    Route::get('/', function () {
        $user = auth()->user();
        $redirectRoute = 'warehouse.inventory';
        
        if ($user && $user->role !== 'admin') {
            $permissions = $user->permissions ?? [];
            $routeMap = [
                'warehouse.inventory' => 'warehouse.inventory',
                'warehouse.stock-out' => 'warehouse.stock-out',
                'warehouse.stock-in' => 'warehouse.stock-in',
                'warehouse.stock-transfer.index' => 'warehouse.stock-transfer.index',
                'warehouse.stock-count' => 'warehouse.stock-count',
                'warehouse.product-catalog' => 'warehouse.product-catalog',
                'warehouse.contacts' => 'warehouse.contacts',
                'warehouse.asset-manager' => 'warehouse.asset-manager',
                'warehouse.maintenance-dashboard' => 'warehouse.maintenance-dashboard',
            ];
            foreach ($routeMap as $perm => $routeName) {
                if (in_array($perm, $permissions)) {
                    $redirectRoute = $routeName;
                    break;
                }
            }
        }
        
        return redirect()->route($redirectRoute);
    });

    // 1. PRODUCT CATALOG
    Route::middleware('permission:warehouse.product-catalog')->group(function() {
        Route::get('/categories', \App\Livewire\Warehouse\CategoryManager::class)->name('categories');
        Route::get('/product-catalog', \App\Livewire\Warehouse\ProductCatalog::class)->name('product-catalog');
        Route::get('/bom', function () {
            return view('warehouse.bom');
        })->name('bom');
        Route::get('/material-names', function () {
            return view('warehouse.material-names');
        })->name('material-names');
    });

    // 2. CONTACTS
    Route::middleware('permission:warehouse.contacts')->group(function() {
        Route::get('/contacts', function () {
            return view('warehouse.customer-management');
        })->name('contacts');
        Route::get('/contacts/print', function() {
            $ids = request('ids');
            if (!$ids) return redirect()->route('warehouse.contacts')->with('error', 'Không có đối tác nào được chọn để in.');
            $contacts = \App\Models\Supplier::whereIn('id', explode(',', $ids))->get();
            return view('warehouse.contacts-print', compact('contacts'));
        })->name('contacts.print');
        Route::get('/customer-debt', function () {
            return view('warehouse.customer-debt');
        })->name('customer-debt');
    });

    // 3. INVENTORY
    Route::middleware('permission:warehouse.inventory')->group(function() {
        Route::get('/inventory', function () {
            return view('warehouse.inventory');
        })->name('inventory');
    });

    // 4. STOCK IN
    Route::middleware('permission:warehouse.stock-in')->group(function() {
        Route::get('/stock-in', function () {
            return view('warehouse.stock-in');
        })->name('stock-in');
    });

    // 5. STOCK OUT
    Route::middleware('permission:warehouse.stock-out')->group(function() {
        Route::get('/stock-out', function () {
            return view('warehouse.stock-out');
        })->name('stock-out');
        Route::get('/delivery-note', \App\Livewire\Warehouse\MaterialRequirement::class)->name('delivery-note');
        Route::get('/delivery-report', function () {
            return view('warehouse.delivery-report');
        })->name('delivery-report');
    });

    // 6. STOCK COUNT
    Route::middleware('permission:warehouse.stock-count')->group(function() {
        Route::get('/stock-count', function () {
            return view('warehouse.stock-count');
        })->name('stock-count');
    });

    // 7. STOCK TRANSFER
    Route::middleware('permission:warehouse.stock-transfer.index')->group(function() {
        Route::get('/stock-transfer', \App\Livewire\Warehouse\StockTransferList::class)->name('stock-transfer.index');
        Route::get('/stock-transfer/create', \App\Livewire\Warehouse\StockTransferForm::class)->name('stock-transfer.create');
        Route::get('/stock-transfer/print/{id}', function($id) {
            $transfer = \App\Models\StockTransfer::with(['creator', 'items.product'])->findOrFail($id);
            return view('warehouse.stock-transfer-print', compact('transfer'));
        })->name('stock-transfer.print');
        Route::get('/stock-transfer/print-bulk', function() {
            $ids = request('ids');
            if (!$ids) return redirect()->route('warehouse.stock-transfer.index')->with('error', 'Không có phiếu nào được chọn');
            $transfers = \App\Models\StockTransfer::with(['creator', 'items.product'])
                ->whereIn('id', explode(',', $ids))->orderBy('created_at', 'desc')->get();
            return view('warehouse.stock-transfer-print-bulk', compact('transfers'));
        })->name('stock-transfer.print-bulk');
    });

    // 8. STOCK RECOVERY
    Route::middleware('permission:warehouse.stock-recovery-report')->group(function() {
        Route::get('/stock-recovery-report', \App\Livewire\Warehouse\StockRecoveryReportList::class)->name('stock-recovery-report');
    });

    // 9. SETTINGS WAREHOUSE
    Route::middleware('permission:warehouse.settings.warehouses')->group(function() {
        Route::get('/settings/warehouses', \App\Livewire\Warehouse\WarehouseManager::class)->name('settings.warehouses');
    });

    // 10. ASSET MANAGER & ODO
    Route::middleware('permission:warehouse.asset-manager')->group(function() {
        Route::get('/asset-dashboard', function () { return view('warehouse.asset.dashboard'); })->name('asset-dashboard');
        Route::get('/asset-manager', \App\Livewire\Warehouse\AssetMaintenanceErp::class)->name('asset-manager');
        Route::get('/asset-bom-manager', function () { return view('warehouse.asset.bom-manager'); })->name('asset-bom-manager');
        Route::get('/maintenance-form', function () { return view('warehouse.asset.maintenance-form'); })->name('maintenance-form');
        Route::get('/odo-manager', \App\Livewire\Warehouse\OdoManager::class)->name('odo-manager');
        
        // Maintenance BOM
        Route::get('/maintenance-boms', \App\Livewire\Maintenance\MaintenanceBomList::class)->name('maintenance-boms.index');
        Route::get('/maintenance-boms/create', \App\Livewire\Maintenance\MaintenanceBomForm::class)->name('maintenance-boms.create');
        Route::get('/maintenance-boms/{bomId}/edit', \App\Livewire\Maintenance\MaintenanceBomForm::class)->name('maintenance-boms.edit');
    });

    Route::middleware('permission:warehouse.asset-odo-log')->group(function() {
        Route::get('/asset-odo-log', \App\Livewire\Warehouse\AssetOdoLog::class)->name('asset-odo-log');
    });

    // 11. MAINTENANCE
    Route::middleware('permission:warehouse.maintenance-dashboard')->group(function() {
        Route::get('/maintenance-dashboard', \App\Livewire\Warehouse\MaintenanceReport::class)->name('maintenance-dashboard');
    });
    Route::middleware('permission:warehouse.maintenance-tracking')->group(function() {
        Route::get('/maintenance-tracking', \App\Livewire\Warehouse\MaintenanceTracking::class)->name('maintenance-tracking');
    });
    Route::middleware('permission:warehouse.maintenance-rules')->group(function() {
        Route::get('/maintenance-rules', \App\Livewire\Warehouse\MaintenanceRuleManager::class)->name('maintenance-rules');
    });
    Route::middleware('permission:warehouse.maintenance-plans')->group(function() {
        Route::get('/maintenance-plans', \App\Livewire\Warehouse\MaintenancePlanManager::class)->name('maintenance-plans');
    });
    Route::middleware('permission:warehouse.maintenance-tickets')->group(function() {
        Route::get('/maintenance-tickets', \App\Livewire\Warehouse\MaintenanceTicketManager::class)->name('maintenance-tickets');
        Route::get('/maintenance-tickets/print', function() {
            $ids = request('ids');
            if (!$ids) return redirect()->route('warehouse.maintenance-tickets')->with('error', 'Không có phiếu nào được chọn.');
            $tickets = \App\Models\MaintenanceTicket::with(['asset', 'items.product'])->whereIn('id', explode(',', $ids))->get();
            return view('warehouse.maintenance.print-tickets', compact('tickets'));
        })->name('maintenance-tickets.print');
    });

    // 12. PURCHASE PLAN
    Route::middleware('permission:warehouse.purchase-plan')->group(function() {
        Route::get('/purchase-plan', function () { return view('warehouse.purchase-plan'); })->name('purchase-plan');
        Route::get('/purchase-plan/print', function (Illuminate\Http\Request $request) {
            $ids = explode(',', $request->query('ids', ''));
            $plans = \App\Models\PurchasePlan::with('product')->whereIn('id', $ids)->get();
            return view('warehouse.purchase-plan-print', compact('plans'));
        })->name('purchase-plan.print');
        Route::get('/purchase-request', function () { return view('warehouse.purchase-request'); })->name('purchase-request');
    });
    Route::middleware('permission:warehouse.purchase-plan.history')->group(function() {
        Route::get('/purchase-plan/history', function () { return view('warehouse.purchase-plan-history'); })->name('purchase-plan.history');
    });

    // 13. REPORTS
    Route::middleware('permission:warehouse.reports.transaction-detail')->group(function() {
        Route::get('/reports/transaction-detail', function () {
            return view('warehouse.transaction-detail-report');
        })->name('reports.transaction-detail');
        Route::get('/reports/transaction-detail/print', function() {
            $ids = request('ids');
            if (!$ids) return redirect()->route('warehouse.reports.transaction-detail')->with('error', 'Không có giao dịch nào được chọn để in.');
            $transactions = \App\Models\InventoryTransaction::with(['product', 'creator', 'reference'])
                ->whereIn('id', explode(',', $ids))->orderBy('created_at', 'desc')->get();
            $assetCodesCount = $transactions->filter(function($tx) { return $tx->reference && isset($tx->reference->asset_code) && !empty($tx->reference->asset_code); })->pluck('reference.asset_code')->unique()->count();
            $productCodesCount = $transactions->filter(function($tx) { return $tx->product_id; })->pluck('product_id')->unique()->count();
            return view('warehouse.transaction-detail-print', compact('transactions', 'assetCodesCount', 'productCodesCount'));
        })->name('reports.transaction-detail.print');
    });

    Route::middleware('permission:warehouse.reports.stock')->group(function() {
        Route::get('/reports/stock', function () {
            return view('warehouse.stock-report');
        })->name('reports.stock');
        Route::get('/reports/daily', \App\Livewire\Warehouse\Reports\DailyReport::class)->name('reports.daily');
        Route::get('/reports/daily/print', function() {
            $date = request('date', now()->format('Y-m-d'));
            $parsedDate = \Carbon\Carbon::parse($date);
            $stockInCount = \App\Models\StockInItem::whereHas('stockIn', function($q) use ($parsedDate) { $q->whereDate('stock_in_date', $parsedDate); })->distinct('product_id')->count('product_id');
            $stockOutCount = \App\Models\StockOutItem::whereHas('stockOut', function($q) use ($parsedDate) { $q->whereDate('created_at', $parsedDate); })->distinct('product_id')->count('product_id');
            $stockTransferCount = \App\Models\StockTransferItem::whereHas('stockTransfer', function($q) use ($parsedDate) { $q->whereDate('transfer_date', $parsedDate); })->distinct('product_id')->count('product_id');
            $stockRecoveryCount = \App\Models\StockRecovery::whereDate('recovery_date', $parsedDate)->distinct('product_id')->count('product_id');
            $totalStockOutOrders = \App\Models\StockOut::whereDate('created_at', $parsedDate)->count();
            $assetExportCount = \App\Models\StockOut::whereDate('created_at', $parsedDate)->whereNotNull('asset_code')->where('asset_code', '!=', '')->distinct('asset_code')->count('asset_code');
            $reportData = [
                'stockInCount' => $stockInCount, 'stockOutCount' => $stockOutCount, 'stockTransferCount' => $stockTransferCount,
                'stockRecoveryCount' => $stockRecoveryCount, 'totalStockOutOrders' => $totalStockOutOrders, 'assetExportCount' => $assetExportCount, 'materialExportCount' => $stockOutCount,
            ];
            return view('warehouse.reports.daily-report-print', compact('reportData', 'date'));
        })->name('reports.daily.print');
    });

    // 14. CHAT
    Route::middleware('permission:warehouse.chat')->group(function() {
        Route::get('/chat', \App\Livewire\Warehouse\WarehouseChat::class)->name('chat');
    });

    // ERP Maintenance route
    Route::get('/maintenance', function () {
        return view('pages.maintenance.index');
    })->name('maintenance.index')->middleware('permission:warehouse.asset-manager');

});
