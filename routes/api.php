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

// Route untuk Testing
Route::get('/test', function () {
    return response()->json([
        'message' => 'API Test Versi 2',
        'status' => 200
    ]);
});

// Route untuk User Authentication
Route::prefix('auth')->group(function () {
    Route::post('/signup', [UserController::class, 'signUp']);
    Route::post('/signin', [UserController::class, 'signIn']);
    Route::middleware('auth:sanctum')->post('/logout', [UserController::class, 'logout']);
    Route::middleware('auth:sanctum')->get('/user', [UserController::class, 'getUserData']);
});

// Route Produk
Route::prefix('products')->group(function () {
    Route::get('/', [ProductController::class, 'index']); // Semua produk
    Route::get('/{id}', [ProductController::class, 'showByProductId']); // Detail produk
    Route::post('/', [ProductController::class, 'store']);
    // Route::post('/', [ProductController::class, 'store'])->middleware('auth:sanctum'); // Tambah produk (Admin)
    Route::delete('/{id}', [ProductController::class, 'destroy']);
});

// Route yang Membutuhkan Autentikasi
Route::middleware('auth:sanctum')->group(function () {
    
    // Wishlist Routes
    Route::prefix('wishlist')->group(function () {
        Route::get('/', [WishlistController::class, 'index']);
        Route::post('/toggle', [WishlistController::class, 'toggle']);
    });

    // Cart Routes
    Route::prefix('cart')->group(function () {
        Route::get('/', [CartController::class, 'index']);
        Route::post('/', [CartController::class, 'store']);
        Route::delete('/{id}', [CartController::class, 'destroy']);
        Route::patch('/{id}/quantity', [CartController::class, 'updateQuantity']);
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

    // Route untuk Review oleh User
    Route::prefix('reviews/{productId}')->group(function () {
        Route::get('/', [ReviewController::class, 'index']); // Semua ulasan dari User
        Route::post('/', [ReviewController::class, 'store']); // Tambahkan ulasan baru
    });
});
