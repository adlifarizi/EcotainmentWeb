<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\PurchaseHistoryController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\SearchHistoryController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\WishlistController;
use Illuminate\Support\Facades\Route;

// Route untuk user authentication (signup, signin, logout)
Route::prefix('auth')->group(function () {
    Route::post('/signup', [UserController::class, 'signUp']);
    Route::post('/signin', [UserController::class, 'signIn']);
    Route::middleware('auth:sanctum')->post('/logout', [UserController::class, 'logout']);
});

// Route untuk product tanpa middleware auth
Route::get('/products', [ProductController::class, 'index']);
Route::post('/products', [ProductController::class, 'store']);
Route::get('/products/{id}', [ProductController::class, 'showByProductId']);


Route::get('/test', function () {
    return response()->json([
        'message' => 'API Test Successful',
        'status' => 200
    ]);
});

// Kelompokkan route yang membutuhkan auth middleware
Route::middleware('auth:sanctum')->group(function () {

    // Wishlist routes
    Route::prefix('wishlist')->group(function () {
        Route::get('/', [WishlistController::class, 'index']);
        Route::post('/', [WishlistController::class, 'store']);
        Route::delete('/{id}', [WishlistController::class, 'destroy']);
    });

    // Cart routes
    Route::prefix('cart')->group(function () {
        Route::get('/', [CartController::class, 'index']);
        Route::post('/', [CartController::class, 'store']);
        Route::delete('/{id}', [CartController::class, 'destroy']);
    });

    // Transaction routes
    Route::prefix('transactions')->group(function () {
        Route::get('/', [TransactionController::class, 'index']);
        Route::post('/', [TransactionController::class, 'store']);
        Route::patch('/{id}/status', [TransactionController::class, 'updateStatus']);
    });

    // Purchase History routes
    Route::prefix('purchase-history')->group(function () {
        Route::get('/', [PurchaseHistoryController::class, 'index']);
        Route::post('/', [PurchaseHistoryController::class, 'store']);
    });

    // Search History routes
    Route::prefix('search-history')->group(function () {
        Route::get('/', [SearchHistoryController::class, 'index']);
        Route::post('/', [SearchHistoryController::class, 'store']);
    });

    // Route menambahkan review pada produk
    Route::prefix('products/{productId}')->group(function () {
        // Route untuk menambah ulasan pada produk
        Route::post('/reviews', [ReviewController::class, 'store']);

        // Route untuk melihat semua ulasan pada produk
        Route::get('/reviews', [ReviewController::class, 'index']);
    });
});
