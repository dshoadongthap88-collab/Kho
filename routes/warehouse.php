<?php

use Illuminate\Support\Facades\Route;

Route::prefix('warehouse')->name('warehouse.')->group(function () {
    Route::get('/', function () {
        return redirect()->route('warehouse.inventory');
    });

    Route::get('/product-catalog', function () {
        return view('warehouse.product-catalog');
    })->name('product-catalog')->middleware('permission:product-catalog');

    Route::get('/contacts', function () {
        return view('warehouse.customer-management');
    })->name('contacts')->middleware('permission:contacts');

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
});
