<?php

use Illuminate\Support\Facades\Route;

Route::prefix('warehouse')->name('warehouse.')->group(function () {
    Route::get('/', function () {
        return redirect()->route('warehouse.inventory');
    });

    Route::get('/categories', \App\Livewire\Warehouse\CategoryManager::class)
        ->name('categories')
        ->middleware('permission:product-catalog'); // Use same permission as product catalog for now

    Route::get('/product-catalog', \App\Livewire\Warehouse\ProductCatalog::class)
        ->name('product-catalog')
        ->middleware('permission:product-catalog');

    Route::get('/contacts', function () {
        return view('warehouse.customer-management');
    })->name('contacts')->middleware('permission:contacts');

    Route::get('/contacts/print', function() {
        $ids = request('ids');
        if (!$ids) {
            return redirect()->route('warehouse.contacts')->with('error', 'Không có đối tác nào được chọn để in.');
        }

        $contacts = \App\Models\Supplier::whereIn('id', explode(',', $ids))->get();
        // Since we need to know what type it is to show the right title:
        // Or we can just let the view handle it based on the items.
        return view('warehouse.contacts-print', compact('contacts'));
    })->name('contacts.print');

    Route::get('/inventory', function () {
        return view('warehouse.inventory');
    })->name('inventory')->middleware('permission:inventory');

    Route::get('/stock-in', function () {
        return view('warehouse.stock-in');
    })->name('stock-in')->middleware('permission:stock-in');

    Route::get('/stock-out', function () {
        return view('warehouse.stock-out');
    })->name('stock-out')->middleware('permission:stock-out');

    Route::get('/stock-count', function () {
        return view('warehouse.stock-count');
    })->name('stock-count')->middleware('permission:stock-count');

    Route::get('/settings/warehouses', \App\Livewire\Warehouse\WarehouseManager::class)->name('settings.warehouses');

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
            ->whereIn('id', explode(',', $ids))
            ->orderBy('created_at', 'desc')
            ->get();
        return view('warehouse.stock-transfer-print-bulk', compact('transfers'));
    })->name('stock-transfer.print-bulk');

    Route::get('/bom', function () {
        return view('warehouse.bom');
    })->name('bom')->middleware('permission:bom');

    Route::get('/material-names', function () {
        return view('warehouse.material-names');
    })->name('material-names')->middleware('permission:material-names');

    Route::get('/purchase-request', function () {
        return view('warehouse.purchase-request');
    })->name('purchase-request')->middleware('permission:purchase-request');

    Route::get('/delivery-note', \App\Livewire\Warehouse\MaterialRequirement::class)->name('delivery-note')->middleware('permission:delivery-note');

    Route::get('/reports/transaction-detail', function () {
        return view('warehouse.transaction-detail-report');
    })->name('reports.transaction-detail')->middleware('permission:reports_transaction');

    Route::get('/reports/stock', function () {
        return view('warehouse.stock-report');
    })->name('reports.stock')->middleware('permission:reports_stock');

    Route::get('/customer-debt', function () {
        return view('warehouse.customer-debt');
    })->name('customer-debt')->middleware('permission:customer-debt');

    Route::get('/delivery-report', function () {
        return view('warehouse.delivery-report');
    })->name('delivery-report')->middleware('permission:delivery-report');

    // Asset Management Routes
    Route::get('/asset-dashboard', function () {
        return view('warehouse.asset.dashboard');
    })->name('asset-dashboard');

    Route::get('/asset-manager', \App\Livewire\Warehouse\AssetMaintenanceErp::class)
        ->name('asset-manager');

    Route::get('/asset-bom-manager', function () {
        return view('warehouse.asset.bom-manager');
    })->name('asset-bom-manager');

    Route::get('/maintenance-form', function () {
        return view('warehouse.asset.maintenance-form');
    })->name('maintenance-form');

    // ODO Management Route
    Route::get('/odo-manager', \App\Livewire\Warehouse\OdoManager::class)->name('odo-manager');

    // Module 4: THEO DÕI BẢO DƯỠNG
    Route::get('/maintenance-dashboard', \App\Livewire\Warehouse\MaintenanceReport::class)->name('maintenance-dashboard');
    Route::get('/maintenance-tracking', \App\Livewire\Warehouse\MaintenanceTracking::class)->name('maintenance-tracking');
    Route::get('/maintenance-rules', \App\Livewire\Warehouse\MaintenanceRuleManager::class)->name('maintenance-rules');
    Route::get('/maintenance-plans', \App\Livewire\Warehouse\MaintenancePlanManager::class)->name('maintenance-plans');
    Route::get('/maintenance-tickets', \App\Livewire\Warehouse\MaintenanceTicketManager::class)->name('maintenance-tickets');
    Route::get('/asset-odo-log', \App\Livewire\Warehouse\AssetOdoLog::class)->name('asset-odo-log');

    // Stock Recovery Report Routes
    Route::get('/stock-recovery-report', \App\Livewire\Warehouse\StockRecoveryReportList::class)->name('stock-recovery-report')->middleware('permission:stock_recovery');

    // Module 6: CHAT KHO
    Route::get('/chat', \App\Livewire\Warehouse\WarehouseChat::class)->name('chat');
});
