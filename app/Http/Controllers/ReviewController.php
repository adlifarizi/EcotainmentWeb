<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ReviewController extends Controller
{
    /**
     * Store a new review
     * 
     * @param Request $request
     * @param int $productId
     * @return JsonResponse
     */
    public function store(Request $request, $productId): JsonResponse
    {
        try {
            // Validate request
            $validated = $request->validate([
                'rating' => 'required|integer|min:1|max:5',
                'comment' => 'nullable|string',
            ]);

            // Check if product exists
            $product = Product::findOrFail($productId);

            // Create new review
            $review = Review::create([
                'user_id' => Auth::id(),
                'product_id' => $product->id,
                'rating' => $validated['rating'],
                'comment' => $validated['comment'] ?? null,
            ]);

            // Load the user relationship
            // $review->load('user');

            return response()->json([
                'status' => 201,
                'message' => 'Ulasan berhasil ditambahkan',
                'data' => $review
            ], 201, [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 404,
                'message' => 'Produk tidak ditemukan'
            ], 404, [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 422,
                'message' => 'Data yang diberikan tidak valid',
                'errors' => $e->errors()
            ], 422, [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Terjadi kesalahan pada server',
                'error' => $e->getMessage()
            ], 500, [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ]);
        }
    }

    /**
     * Get all reviews for a product
     * 
     * @param int $productId
     * @return JsonResponse
     */
    public function index($productId): JsonResponse
    {
        try {
            $product = Product::findOrFail($productId);
            
            $reviews = $product->reviews()
                ->with('user')
                ->latest()
                ->get();

            return response()->json([
                'status' => 200,
                'message' => 'Berhasil mengambil ulasan produk',
                'data' => [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'average_rating' => $reviews->avg('rating'),
                    'total_reviews' => $reviews->count(),
                    'reviews' => $reviews
                ]
            ], 200, [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 404,
                'message' => 'Produk tidak ditemukan'
            ], 404, [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Terjadi kesalahan pada server',
                'error' => $e->getMessage()
            ], 500, [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ]);
        }
    }
}