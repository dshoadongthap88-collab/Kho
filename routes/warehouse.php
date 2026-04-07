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

    Route::get('/reports', function () {
        return view('warehouse.reports');
    })->name('reports');
});
