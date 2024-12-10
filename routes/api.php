<?php

use App\Http\Controllers\BankController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\PurchaseHistoryController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\SearchHistoryController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\WishlistController;
use App\Http\Middleware\IsAdmin;
use Illuminate\Support\Facades\Route;

// Route untuk Testing
Route::get('/test', function () {
    return response()->json([
        'message' => 'Test API Ver 1.8.7',
        'status' => 200
    ]);
});

// Route untuk User Authentication
Route::prefix('auth')->group(function () {
    Route::post('/signup', [UserController::class, 'signUp']);
    Route::post('/signin', [UserController::class, 'signIn']);
    Route::middleware('auth:sanctum')->post('/logout', [UserController::class, 'logout']);
    Route::middleware('auth:sanctum')->get('/user', [UserController::class, 'getUserData']);
    Route::middleware('auth:sanctum')->put('/profile', [UserController::class, 'updateProfile']);
    Route::middleware('auth:sanctum')->get('/address', [UserController::class, 'getAddress']);
    Route::middleware('auth:sanctum')->post('/address', [UserController::class, 'addAddress']);
    Route::middleware('auth:sanctum')->put('/address/{addressId}', [UserController::class, 'editAddress']);
    Route::middleware('auth:sanctum')->delete('/address/{addressId}', [UserController::class, 'deleteAddress']);
});

// Route Produk
Route::prefix('products')->group(function () {
    Route::get('/', [ProductController::class, 'index']); // Semua produk
    Route::get('/{id}', [ProductController::class, 'showByProductId']); // Detail produk
});

// Route Bank
Route::prefix('bank')->group(function () {
    Route::get('/', [BankController::class, 'index']);
    Route::get('/{bankId}', [BankController::class, 'show']);
});

//Route untuk Admin
Route::middleware(['auth:sanctum', IsAdmin::class])->group(function () {

    Route::prefix('admin')->group(function () {
        // Product Related
        Route::post('/product', [ProductController::class, 'store']);
        Route::put('/product/{id}', [ProductController::class, 'update']);
        Route::delete('/product/{id}', [ProductController::class, 'destroy']);

        // Transaction Related
        Route::get('/transactions', [TransactionController::class, 'getAllTransactions']);
        Route::put('/transactions/{id}/status', [TransactionController::class, 'adminUpdateTransactionStatus']);

        // Bank Related
        Route::post('/banks', [BankController::class, 'store']);
        Route::put('/banks/{bankId}', [BankController::class, 'update']);
        Route::delete('/banks/{bankId}', [BankController::class, 'destroy']);
    });
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
        Route::post('/filter-by-products', [CartController::class, 'showByProductIds']);
    });

    // Transaction Routes
    Route::prefix('transactions')->group(function () {
        Route::get('/', [TransactionController::class, 'index']);
        Route::post('/', [TransactionController::class, 'store']);
        Route::patch('/{id}/status', [TransactionController::class, 'updateStatus']);
        Route::post('/{id}/proof', [TransactionController::class, 'uploadPaymentProof']);
    });

    // Purchase History Routes
    Route::prefix('purchase-history')->group(function () {
        Route::get('/', [PurchaseHistoryController::class, 'index']);
    });

    // Search History Routes
    Route::prefix('search-history')->group(function () {
        Route::get('/', [SearchHistoryController::class, 'index']);
        Route::post('/', [SearchHistoryController::class, 'store']);
    });

    // Route untuk Review oleh User
    Route::prefix('reviews/{productId}')->group(function () {
        Route::get('/', [ReviewController::class, 'index']); // Semua ulasan dari User per Product
        Route::post('/', [ReviewController::class, 'store']); // Tambahkan ulasan baru
    });
});
