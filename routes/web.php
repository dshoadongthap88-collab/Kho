<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\Admin\UserController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Login Routes (no auth required)
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login')->middleware('guest');
Route::post('/login', [LoginController::class, 'login'])->middleware('guest');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// Protected Routes (require auth)
Route::middleware('auth')->group(function () {
    // Tenant Routes
    Route::get('/select-house', [\App\Http\Controllers\TenantController::class, 'selectHouse'])->name('tenant.select-house');
    Route::post('/verify-house', [\App\Http\Controllers\TenantController::class, 'verifyHouse'])->name('tenant.verify-house');

    // Tenant Protected Routes
    Route::middleware('tenant')->group(function () {
        Route::get('/', function () {
            return redirect()->route('warehouse.inventory');
        });

        // Password Routes
        Route::get('/password/edit', [PasswordController::class, 'edit'])->name('password.edit');
        Route::put('/password/update', [PasswordController::class, 'update'])->name('password.update');
        Route::post('/admin/password/reset/{user}', [PasswordController::class, 'resetUserPassword'])->name('password.reset')->middleware('admin');

        // Admin Routes
        Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
            // (Đã chuyển qua HR)
        });

        // Maintenance rules, tickets, plans...
        Route::get('/maintenance-rules', \App\Livewire\Warehouse\MaintenanceRuleManager::class)->name('maintenance-rules');
        Route::get('/maintenance-tracking', \App\Livewire\Warehouse\MaintenanceTracking::class)->name('maintenance-tracking');
        Route::get('/maintenance-plans', \App\Livewire\Warehouse\MaintenancePlanManager::class)->name('maintenance-plans');
        Route::get('/maintenance-tickets', \App\Livewire\Warehouse\MaintenanceTicketManager::class)->name('maintenance-tickets');
        Route::get('/maintenance-dashboard', \App\Livewire\Warehouse\MaintenanceDashboard::class)->name('maintenance-dashboard');
        
        // Purchase Plan Module
        Route::get('/purchase-plan', function () {
            return view('warehouse.purchase-plan');
        })->name('purchase-plan');
        
        Route::get('/purchase-plan/print', function (Illuminate\Http\Request $request) {
            $ids = explode(',', $request->query('ids', ''));
            $plans = \App\Models\PurchasePlan::with('product')->whereIn('id', $ids)->get();
            return view('warehouse.purchase-plan-print', compact('plans'));
        })->name('purchase-plan.print');
        
        Route::get('/purchase-plan/history', function () {
            return view('warehouse.purchase-plan-history');
        })->name('purchase-plan.history');

        // Module Báo Cáo
        Route::get('/purchase-request', \App\Livewire\Warehouse\PurchaseOrderList::class)->name('purchase-request');

        // HR House Routes (Quản trị & Phân quyền)
        Route::middleware('admin')->prefix('hr')->name('hr.')->group(function () {
            Route::get('/dashboard', \App\Livewire\Hr\HrDashboard::class)->name('dashboard');
            Route::get('/projects', \App\Livewire\Hr\ProjectManager::class)->name('projects');
            Route::get('/permissions', \App\Livewire\Hr\PermissionManager::class)->name('permissions');
            Route::get('/modules', \App\Livewire\Hr\SystemModuleManager::class)->name('modules');
            Route::get('/global-report', \App\Livewire\Hr\GlobalReport::class)->name('global-report');
            Route::get('/notifications', \App\Livewire\Hr\NotificationManager::class)->name('notifications');
            Route::get('/purchase-center', \App\Livewire\Hr\PurchaseCenter::class)->name('purchase-center');
            
            Route::get('/departments', \App\Livewire\Hr\DepartmentManager::class)->name('departments');
            Route::get('/users', \App\Livewire\Hr\UserManager::class)->name('users');
        });

        // Maintenance ERP Route
        Route::get('/maintenance', function () {
            return view('pages.maintenance.index');
        })->name('maintenance.index');

        // Maintenance BOM
        Route::get('/maintenance-boms', \App\Livewire\Maintenance\MaintenanceBomList::class)->name('maintenance-boms.index');
        Route::get('/maintenance-boms/create', \App\Livewire\Maintenance\MaintenanceBomForm::class)->name('maintenance-boms.create');
        Route::get('/maintenance-boms/{bomId}/edit', \App\Livewire\Maintenance\MaintenanceBomForm::class)->name('maintenance-boms.edit');

        require __DIR__.'/warehouse.php';
    });
});
Route::get('/test-agent-ping', function() { return "pong-from-d-project"; });
