<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\SupplierController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\SaleController;
use App\Http\Controllers\Api\PurchaseController;
use App\Http\Controllers\Api\StockMovementController;
use App\Http\Controllers\Api\CashRegisterController;
use App\Http\Controllers\Api\CashMovementController;
use App\Http\Controllers\Api\AccountReceivableController;
use App\Http\Controllers\Api\AccountPayableController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ReportController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:3,60');
Route::post('/send-otp', [AuthController::class, 'sendOtp'])->middleware('throttle:3,1');
Route::post('/verify-otp', [AuthController::class, 'verifyOtp'])->middleware('throttle:5,1');
Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->middleware('throttle:3,60');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->middleware('throttle:3,60');

Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);

    Route::apiResource('customers', CustomerController::class);
    Route::apiResource('suppliers', SupplierController::class);
    Route::apiResource('categories', CategoryController::class);
    Route::get('/products/low-stock', [ProductController::class, 'lowStock']);
    Route::apiResource('products', ProductController::class);
    Route::apiResource('sales', SaleController::class);
    Route::apiResource('purchases', PurchaseController::class);
    Route::apiResource('stock-movements', StockMovementController::class);

    Route::get('/cash-registers', [CashRegisterController::class, 'index']);
    Route::post('/cash-registers', [CashRegisterController::class, 'store']);
    Route::get('/cash-registers/{cashRegister}', [CashRegisterController::class, 'show']);
    Route::post('/cash-registers/{cashRegister}/close', [CashRegisterController::class, 'close']);

    Route::apiResource('cash-movements', CashMovementController::class);
    Route::apiResource('accounts-receivable', AccountReceivableController::class);
    Route::post('/accounts-receivable/{accountReceivable}/pay', [AccountReceivableController::class, 'markAsPaid']);
    Route::apiResource('accounts-payable', AccountPayableController::class);
    Route::post('/accounts-payable/{accountPayable}/pay', [AccountPayableController::class, 'markAsPaid']);

    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);

    Route::get('/reports/sales', [ReportController::class, 'sales']);
    Route::get('/reports/products', [ReportController::class, 'products']);
    Route::get('/reports/financial', [ReportController::class, 'financial']);
});
