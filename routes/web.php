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
    Route::get('/', function () {
        return redirect()->route('warehouse.inventory');
    });

    // Password Routes
    Route::get('/password/edit', [PasswordController::class, 'edit'])->name('password.edit');
    Route::put('/password/update', [PasswordController::class, 'update'])->name('password.update');
    Route::post('/admin/password/reset/{user}', [PasswordController::class, 'resetUserPassword'])->name('password.reset')->middleware('admin');

    // Admin Routes
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::resource('users', UserController::class)->except(['create', 'show', 'edit']);
    });

    require __DIR__.'/warehouse.php';
});
