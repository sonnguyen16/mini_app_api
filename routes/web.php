<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\AppController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\VoucherController;
use App\Http\Controllers\Admin\PolicyController;

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

Route::get('/', function () {
    if (Auth::check()) {
        return redirect('/admin/dashboard');
    }
    return redirect('/admin/login');
});

// Admin routes
Route::prefix('admin')->name('admin.')->group(function () {
    // Auth routes
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Protected admin routes
    Route::middleware(['auth', 'role:admin|app_owner'])->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Apps (chỉ admin)
        Route::middleware('role:admin')->group(function () {
            Route::resource('apps', AppController::class);
            // update with post
            Route::post('/apps/{app}/update', [AppController::class, 'update'])->name('apps.update');
            Route::post('/apps/{app}/reset-password', [AppController::class, 'resetPassword'])->name('apps.reset-password');
            Route::post('/apps/{app}/toggle-status', [AppController::class, 'toggleStatus'])->name('apps.toggle-status');
        });

        // Users
        Route::resource('users', UserController::class)->parameters(['users' => 'profile']);
        Route::post('/users/add-points', [UserController::class, 'addPoints'])->name('users.add-points');
        Route::post('/users/find-by-qr', [UserController::class, 'findByQR'])->name('users.find-by-qr');

        // Categories
        Route::resource('categories', CategoryController::class);

        // Vouchers
        Route::resource('vouchers', VoucherController::class);
        // update with post
        Route::post('/vouchers/{voucher}/update', [VoucherController::class, 'update'])->name('vouchers.update');
        Route::patch('/vouchers/{voucher}/toggle-status', [VoucherController::class, 'toggleStatus'])->name('vouchers.toggle-status');
        Route::get('/vouchers/categories/{app}', [VoucherController::class, 'getCategoriesByApp'])->name('vouchers.categories');

        // Policies
        Route::get('/policies', [PolicyController::class, 'index'])->name('policies.index');
        Route::get('/policies/create', [PolicyController::class, 'create'])->name('policies.create');
        Route::post('/policies', [PolicyController::class, 'store'])->name('policies.store');
        Route::get('/policies/{policy}/edit', [PolicyController::class, 'edit'])->name('policies.edit');
        Route::put('/policies/{policy}', [PolicyController::class, 'update'])->name('policies.update');
        Route::delete('/policies/{policy}', [PolicyController::class, 'destroy'])->name('policies.destroy');
    });
});
