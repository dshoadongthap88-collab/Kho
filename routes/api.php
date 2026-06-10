<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\InventoryController;
use App\Http\Controllers\Api\StockInController;
use App\Http\Controllers\Api\StockOutController;
use App\Http\Controllers\Api\StockTransferController;
use App\Http\Controllers\Api\StockCountController;
use App\Http\Controllers\Api\WarehouseController;
use App\Http\Controllers\Api\WarehouseReportController;
use App\Http\Controllers\Api\CrmController;
use App\Http\Controllers\Api\AssetController;
use App\Http\Controllers\Api\HrmController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\NotificationController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

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

    // Stock Transfer
    Route::get('/stock-transfers', [StockTransferController::class, 'index']);
    Route::post('/stock-transfers', [StockTransferController::class, 'store']);
    Route::get('/stock-transfers/{transfer}', [StockTransferController::class, 'show']);
    Route::post('/stock-transfers/{transfer}/confirm', [StockTransferController::class, 'confirm']);
    Route::post('/stock-transfers/{transfer}/cancel', [StockTransferController::class, 'cancel']);

    // Stock Count
    Route::get('/stock-counts', [StockCountController::class, 'index']);
    Route::post('/stock-counts', [StockCountController::class, 'store']);
    Route::get('/stock-counts/{count}', [StockCountController::class, 'show']);
    Route::post('/stock-counts/daily/submit', [StockCountController::class, 'submitDaily']);
    Route::get('/stock-counts/daily/today', [StockCountController::class, 'getTodayDaily']);
    Route::post('/stock-counts/{count}/finalize', [StockCountController::class, 'finalize']);

    // Warehouse
    Route::get('/warehouses', [WarehouseController::class, 'index']);
    Route::post('/warehouses', [WarehouseController::class, 'store']);
    Route::get('/warehouses/{warehouse}', [WarehouseController::class, 'show']);
    Route::put('/warehouses/{warehouse}', [WarehouseController::class, 'update']);
    Route::delete('/warehouses/{warehouse}', [WarehouseController::class, 'destroy']);

    // Warehouse Reports
    Route::get('/reports/warehouse/dashboard', [WarehouseReportController::class, 'dashboard']);
    Route::get('/reports/warehouse/all', [WarehouseReportController::class, 'allWarehouses']);
    Route::get('/reports/warehouse/{warehouseId}/inventory', [WarehouseReportController::class, 'inventoryByWarehouse']);
    Route::get('/reports/warehouse/movements', [WarehouseReportController::class, 'stockMovements']);
    Route::get('/reports/warehouse/monthly-stats', [WarehouseReportController::class, 'monthlyStats']);

    // CRM - Khách hàng / Nhà cung cấp / Đơn hàng
    Route::prefix('crm')->group(function () {
        Route::get('/customers', [CrmController::class, 'customers']);
        Route::post('/customers', [CrmController::class, 'storeCustomer']);
        Route::get('/customers/{customer}', [CrmController::class, 'showCustomer']);
        Route::put('/customers/{customer}', [CrmController::class, 'updateCustomer']);
        Route::delete('/customers/{customer}', [CrmController::class, 'destroyCustomer']);

        Route::get('/suppliers', [CrmController::class, 'suppliers']);
        Route::post('/suppliers', [CrmController::class, 'storeSupplier']);
        Route::get('/suppliers/{supplier}', [CrmController::class, 'showSupplier']);
        Route::put('/suppliers/{supplier}', [CrmController::class, 'updateSupplier']);
        Route::delete('/suppliers/{supplier}', [CrmController::class, 'destroySupplier']);

        Route::get('/purchase-orders', [CrmController::class, 'purchaseOrders']);
        Route::post('/purchase-orders', [CrmController::class, 'storePurchaseOrder']);
        Route::get('/purchase-orders/{purchaseOrder}', [CrmController::class, 'showPurchaseOrder']);
    });

    // Assets - Tài sản / Thiết bị
    Route::prefix('assets')->group(function () {
        Route::get('/maintenance-tickets', [AssetController::class, 'maintenanceTickets']);
        Route::post('/maintenance-tickets', [AssetController::class, 'storeMaintenanceTicket']);
        Route::post('/meter-readings', [AssetController::class, 'storeMeterReading']);

        Route::get('/', [AssetController::class, 'index']);
        Route::post('/', [AssetController::class, 'store']);
        Route::get('/{asset}', [AssetController::class, 'show']);
        Route::put('/{asset}', [AssetController::class, 'update']);
        Route::delete('/{asset}', [AssetController::class, 'destroy']);
    });

    // HRM - Nhân sự
    Route::prefix('hrm')->group(function () {
        Route::get('/employees', [HrmController::class, 'employees']);
        Route::get('/employees/{user}', [HrmController::class, 'showEmployee']);
        Route::put('/employees/{user}', [HrmController::class, 'updateEmployee']);

        Route::get('/attendances', [HrmController::class, 'attendances']);
        Route::post('/attendances/clock-in', [HrmController::class, 'clockIn']);
        Route::post('/attendances/clock-out', [HrmController::class, 'clockOut']);

        Route::get('/leave-requests', [HrmController::class, 'leaveRequests']);
        Route::post('/leave-requests', [HrmController::class, 'storeLeaveRequest']);
        Route::get('/leave-requests/{leaveRequest}', [HrmController::class, 'showLeaveRequest']);
        Route::post('/leave-requests/{leaveRequest}/approve', [HrmController::class, 'approveLeaveRequest']);
        Route::post('/leave-requests/{leaveRequest}/reject', [HrmController::class, 'rejectLeaveRequest']);
    });

    // API giữ lại mặc định của Laravel
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});

// Chat - Tin nhắn nội bộ
Route::prefix('chat')->middleware('auth:sanctum')->group(function () {
    Route::get('/', [ChatController::class, 'index']);
    Route::post('/', [ChatController::class, 'store']);
    Route::post('/{id}/read', [ChatController::class, 'markAsRead']);
    Route::get('/unread-count', [ChatController::class, 'unreadCount']);
});

// Notifications - Thông báo
Route::prefix('notifications')->middleware('auth:sanctum')->group(function () {
    Route::get('/', [NotificationController::class, 'index']);
    Route::get('/unread-count', [NotificationController::class, 'unreadCount']);
    Route::get('/{id}', [NotificationController::class, 'show']);
    Route::post('/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/read-all', [NotificationController::class, 'markAllAsRead']);
});