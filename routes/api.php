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

// Route untuk User Authentication (Tanpa Middleware)
Route::prefix('auth')->group(function () {
    Route::post('/signup', [UserController::class, 'signUp']);
    Route::post('/signin', [UserController::class, 'signIn']);
    Route::middleware('auth:sanctum')->post('/logout', [UserController::class, 'logout']);
});

// Route Produk Tanpa Middleware (Public Routes)
Route::prefix('products')->group(function () {
    Route::get('/', [ProductController::class, 'index']); // Semua produk
    Route::get('/{id}', [ProductController::class, 'showByProductId']); // Detail produk
    Route::post('/', [ProductController::class, 'store'])->middleware('auth:sanctum'); // Tambah produk (Admin)
});

// Route untuk Testing
Route::get('/test', function () {
    return response()->json([
        'message' => 'API Test Successful',
        'status' => 200
    ]);
});

// Route yang Membutuhkan Autentikasi
Route::middleware('auth:sanctum')->group(function () {
    
    // Wishlist Routes
    Route::prefix('wishlist')->group(function () {
        Route::get('/', [WishlistController::class, 'index']);
        Route::post('/', [WishlistController::class, 'store']);
        Route::delete('/{id}', [WishlistController::class, 'destroy']);
    });

    // Cart Routes
    Route::prefix('cart')->group(function () {
        Route::get('/', [CartController::class, 'index']);
        Route::post('/', [CartController::class, 'store']);
        Route::delete('/{id}', [CartController::class, 'destroy']);
    });

    // Transaction Routes
    Route::prefix('transactions')->group(function () {
        Route::get('/', [TransactionController::class, 'index']);
        Route::post('/', [TransactionController::class, 'store']);
        Route::patch('/{id}/status', [TransactionController::class, 'updateStatus']);
    });

    // Purchase History Routes
    Route::prefix('purchase-history')->group(function () {
        Route::get('/', [PurchaseHistoryController::class, 'index']);
        Route::post('/', [PurchaseHistoryController::class, 'store']);
    });

    // Search History Routes
    Route::prefix('search-history')->group(function () {
        Route::get('/', [SearchHistoryController::class, 'index']);
        Route::post('/', [SearchHistoryController::class, 'store']);
    });

    // Route untuk Review Produk (Nested di dalam /products)
    Route::prefix('products/{productId}')->group(function () {
        Route::get('/reviews', [ReviewController::class, 'index']); // Semua ulasan untuk produk
        Route::post('/reviews', [ReviewController::class, 'store']); // Tambahkan ulasan baru
    });
});
