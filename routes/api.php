<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\VoucherController;
use App\Http\Controllers\Api\PolicyController;

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

Route::prefix('v1')->group(function () {
    // Auth routes
    Route::post('/auth/login', [AuthController::class, 'login']); // Đăng nhập qua Zalo
    Route::post('/auth/login-password', [AuthController::class, 'loginWithPassword']); // Đăng nhập truyền thống

    // Protected routes với app scope
    Route::middleware(['auth:sanctum', 'app.scope'])->group(function () {
        // Profile
        Route::get('/me', [ProfileController::class, 'me']);
        Route::put('/me', [ProfileController::class, 'update']);

        // Categories
        Route::get('/categories', [CategoryController::class, 'index']);

        // Vouchers
        Route::get('/vouchers', [VoucherController::class, 'index']);
        Route::get('/vouchers/latest', [VoucherController::class, 'latest']);
        Route::post('/vouchers/{id}/redeem', [VoucherController::class, 'redeem']);
        Route::post('/wallet/{code}/use', [VoucherController::class, 'use']);
        Route::get('/wallet', [VoucherController::class, 'wallet']);
        Route::get('/history', [VoucherController::class, 'history']);

        // Policies
        Route::get('/policies/membership', [PolicyController::class, 'membership']);
        Route::get('/policies/privacy', [PolicyController::class, 'privacy']);
    });
});
