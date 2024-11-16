<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Storage;

class ProductController extends Controller
{
    public function index(): JsonResponse
    {
        $products = Product::with('reviews')
            ->withAvg('reviews', 'rating')
            ->get()
            ->map(function ($product) {
                // Pastikan rata-rata rating tidak null
                $averageRating = $product->reviews_avg_rating ?? 0;

                // Format rata-rata rating ke 1 angka desimal
                $averageRating = number_format($averageRating, 1);

                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->price,
                    'category' => $product->category,
                    'description' => $product->description,
                    'image' => $product->image,
                    'total_sales' => $product->total_sales,
                    'average_rating' => $averageRating,
                    'created_at' => $product->created_at,
                    'updated_at' => $product->updated_at,
                ];
            });

        return response()->json([
            'success' => true,
            'message' => 'Berhasil mengambil semua produk',
            'data' => $products,
        ], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'category' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|file|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $imageUrl = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('images', 'public');
            $imageUrl = Storage::url($imagePath);
        }

        $product = Product::create([
            'name' => $request->name,
            'price' => $request->price,
            'category' => $request->category,
            'description' => $request->description,
            'image' => $imageUrl,
            'total_sales' => 0,
        ]);

        return response()->json([
            'ssuccess' => true,
            'message' => 'Produk berhasil ditambahkan',
            'data' => $product,
        ], 201);
    }

    public function showByProductId($id): JsonResponse
    {
        try {
            $product = Product::with([
                'reviews.user',
                'reviews' => function ($query) {
                    $query->select('id', 'user_id', 'product_id', 'rating', 'comment', 'created_at');
                }
            ])
                ->withAvg('reviews', 'rating')
                ->findOrFail($id);

            $averageRating = number_format($product->reviews_avg_rating ?? 0, 1);

            return response()->json([
                'success' => true,
                'message' => 'Berhasil mengambil produk dan ulasan',
                'data' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->price,
                    'category' => $product->category,
                    'description' => $product->description,
                    'image' => $product->image,
                    'total_sales' => $product->total_sales,
                    'average_rating' => $averageRating,
                    'reviews' => $product->reviews,
                    'created_at' => $product->created_at,
                    'updated_at' => $product->updated_at,
                ],
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Produk tidak ditemukan',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan pada server',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
