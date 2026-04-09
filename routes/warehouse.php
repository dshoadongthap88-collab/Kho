<?php

use Illuminate\Support\Facades\Route;

Route::prefix('warehouse')->name('warehouse.')->group(function () {
    Route::get('/', function () {
        return redirect()->route('warehouse.inventory');
    });

    Route::get('/product-catalog', function () {
        return view('warehouse.product-catalog');
    })->name('product-catalog');

    Route::get('/contacts', function () {
        return view('warehouse.contact-manager');
    })->name('contacts');

    Route::get('/inventory', function () {
        return view('warehouse.inventory');
    })->name('inventory');

    Route::get('/stock-in', function () {
        return view('warehouse.stock-in');
    })->name('stock-in');

    Route::get('/stock-out', function () {
        return view('warehouse.stock-out');
    })->name('stock-out');

    Route::get('/stock-count', function () {
        return view('warehouse.stock-count');
    })->name('stock-count');

    Route::get('/bom', function () {
        return view('warehouse.bom');
    })->name('bom');

    Route::get('/material-names', function () {
        return view('warehouse.material-names');
    })->name('material-names');

    Route::get('/purchase-request', function () {
        return view('warehouse.purchase-request');
    })->name('purchase-request');

    Route::get('/delivery-note', function () {
        return view('warehouse.delivery-note');
    })->name('delivery-note');

    Route::get('/reports', function () {
        return view('warehouse.reports');
    })->name('reports');

    Route::get('/customer-debt', function () {
        return view('warehouse.customer-debt');
    })->name('customer-debt');

    Route::get('/delivery-report', function () {
        return view('warehouse.delivery-report');
    })->name('delivery-report');
});
