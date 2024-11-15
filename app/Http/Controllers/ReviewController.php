<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function store(Request $request, $productId)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string',
        ]);

        // Cek apakah produk ada
        $product = Product::findOrFail($productId);

        // Buat ulasan baru
        $review = Review::create([
            'user_id' => Auth::id(),
            'product_id' => $product->id,
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        return response()->json([
            'status' => 201,
            'message' => 'Ulasan berhasil ditambahkan',
            'data' => $review,
        ], 201);
    }

    public function index($productId)
    {
        $product = Product::findOrFail($productId);
        $reviews = $product->reviews()->with('user')->get();

        return response()->json([
            'status' => 200,
            'message' => 'Berhasil mengambil ulasan produk',
            'data' => $reviews,
        ], 200);
    }
}
