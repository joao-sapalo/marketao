<?php

use App\Http\Controllers\Api\AccountPayableController;
use App\Http\Controllers\Api\AccountReceivableController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CashMovementController;
use App\Http\Controllers\Api\CashRegisterController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\PurchaseController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\SaleController;
use App\Http\Controllers\Api\StockMovementController;
use App\Http\Controllers\Api\StoreController;
use App\Http\Controllers\Api\SupplierController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:3,60');
Route::post('/send-otp', [AuthController::class, 'sendOtp'])->middleware('throttle:3,1');
Route::post('/verify-otp', [AuthController::class, 'verifyOtp'])->middleware('throttle:5,1');
Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->middleware('throttle:3,60');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->middleware('throttle:3,60');

// ─────────────────────────────────────────────────
// LOJA PÚBLICA — acesso por /s/{store_slug}
// ─────────────────────────────────────────────────
Route::prefix('s/{store_slug}')->group(function () {
    Route::get('/', [App\Http\Controllers\Api\Storefront\HomeController::class, 'index']);
    Route::get('/products', [App\Http\Controllers\Api\Storefront\ProductController::class, 'index']);
    Route::get('/products/{product}', [App\Http\Controllers\Api\Storefront\ProductController::class, 'show']);
    Route::get('/categories', [App\Http\Controllers\Api\Storefront\HomeController::class, 'categories']);
    Route::get('/search', [App\Http\Controllers\Api\Storefront\ProductController::class, 'search']);
    
    Route::get('/cart', [App\Http\Controllers\Api\Storefront\CartController::class, 'show']);
    Route::post('/cart', [App\Http\Controllers\Api\Storefront\CartController::class, 'update']);
    Route::delete('/cart', [App\Http\Controllers\Api\Storefront\CartController::class, 'destroy']);
    Route::post('/cart/interpret', [App\Http\Controllers\Api\Storefront\CartController::class, 'interpret'])->middleware('throttle:10,1');
    
    Route::get('/orders', [App\Http\Controllers\Api\Storefront\OrderController::class, 'index']);
    Route::post('/orders', [App\Http\Controllers\Api\Storefront\OrderController::class, 'store'])->middleware('throttle:10,1');
    Route::get('/orders/{order}', [App\Http\Controllers\Api\Storefront\OrderController::class, 'show']);
    Route::get('/orders/by-reference/{reference}', [App\Http\Controllers\Api\Storefront\OrderController::class, 'byReference']);
    Route::post('/orders/{order}/payment-proof', [App\Http\Controllers\Api\Storefront\PaymentProofController::class, 'store']);
    Route::post('/orders/{order}/review', [App\Http\Controllers\Api\Storefront\ReviewController::class, 'store']);
});

// ─────────────────────────────────────────────────
// DASHBOARD — gestão pelo comerciante (autenticado)
// ─────────────────────────────────────────────────
Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);

    Route::apiResource('stores', StoreController::class);

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

    // ─── Dashboard - Loja ───
    Route::prefix('dashboard')->group(function () {
        Route::get('store', [App\Http\Controllers\Api\Dashboard\StoreController::class, 'show']);
        Route::put('store', [App\Http\Controllers\Api\Dashboard\StoreController::class, 'update']);
        
        Route::get('store-products', [App\Http\Controllers\Api\Dashboard\StoreProductController::class, 'index']);
        Route::put('store-products/{product}', [App\Http\Controllers\Api\Dashboard\StoreProductController::class, 'update']);
        
        Route::get('orders', [App\Http\Controllers\Api\Dashboard\OrderController::class, 'index']);
        Route::get('orders/{order}', [App\Http\Controllers\Api\Dashboard\OrderController::class, 'show']);
        Route::post('orders/{order}/confirm', [App\Http\Controllers\Api\Dashboard\OrderController::class, 'confirm']);
        Route::post('orders/{order}/verify-payment', [App\Http\Controllers\Api\Dashboard\OrderController::class, 'verifyPayment']);
        Route::post('orders/{order}/mark-processing', [App\Http\Controllers\Api\Dashboard\OrderController::class, 'markProcessing']);
        Route::post('orders/{order}/mark-shipped', [App\Http\Controllers\Api\Dashboard\OrderController::class, 'markShipped']);
        Route::post('orders/{order}/mark-delivered', [App\Http\Controllers\Api\Dashboard\OrderController::class, 'markDelivered']);
        Route::post('orders/{order}/cancel', [App\Http\Controllers\Api\Dashboard\OrderController::class, 'cancel']);
        
        Route::get('insights', [App\Http\Controllers\Api\Dashboard\InsightController::class, 'show']);
    });
});
