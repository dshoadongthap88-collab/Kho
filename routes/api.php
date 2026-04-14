<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\InventoryController;
use App\Http\Controllers\Api\StockInController;
use App\Http\Controllers\Api\StockOutController;

// Nhóm API không cần xác thực (Public)
Route::post('/auth/login', [AuthController::class, 'login']);

// Nhóm API cần xác thực bằng Token (Protected)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    
    // Dashboard
    Route::get('/dashboard/summary', [DashboardController::class, 'summary']);
    
    // Inventory
    Route::get('/inventory', [InventoryController::class, 'index']);
    Route::get('/inventory/{id}/history', [InventoryController::class, 'history']);
    
    // Stock In
    Route::get('/stock-in', [StockInController::class, 'index']);
    Route::post('/stock-in', [StockInController::class, 'store']);
    
    // Stock Out
    Route::get('/stock-out', [StockOutController::class, 'index']);
    Route::post('/stock-out', [StockOutController::class, 'store']);
    
    // API giữ lại mặc định của Laravel
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});
