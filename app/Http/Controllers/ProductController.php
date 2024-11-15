<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Storage;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with('reviews')
            ->withAvg('reviews', 'rating') // Menambahkan rata-rata rating
            ->get();

        // Map data untuk menambahkan average_rating ke dalam setiap product
        $products = $products->map(function ($product) {
            // Format rata-rata rating
            $averageRating = $product->reviews_avg_rating;

            // Cek jika averageRating tidak null
            if ($averageRating !== null) {
                $averageRating = number_format($averageRating, 1); // Selalu format dengan 1 angka di belakang koma
            }

            return [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'category' => $product->category,
                'description' => $product->description,
                'image' => $product->image,
                'total_sales' => $product->total_sales,
                'average_rating' => $averageRating, // Rata-rata rating yang sudah diformat
                'created_at' => $product->created_at,
                'updated_at' => $product->updated_at,
            ];
        });


        return response()->json([
            'status' => 200,
            'message' => 'Berhasil mengambil semua produk',
            'data' => $products,
        ], 200);
    }


    public function store(Request $request)
    {
        // Validasi input
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'category' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|file|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Jika ada gambar, simpan ke folder publik dan ambil path-nya
        $imageUrl = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('images', 'public');

            // Ambil URL gambar yang bisa diakses publik
            $imageUrl = Storage::url($imagePath);
        }

        // Simpan data produk ke database
        $product = Product::create([
            'name' => $request->name,
            'price' => $request->price,
            'category' => $request->category,
            'description' => $request->description,
            'image' => $imageUrl,
            'total_sales' => 0, // total_sales diset default ke 0 saat produk baru ditambahkan
        ]);

        return response()->json([
            'status' => 201,
            'message' => 'Produk berhasil ditambahkan',
            'data' => $product,
        ], 201);
    }

    public function showByProductId($id): JsonResponse
{
    try {
        $product = Product::with(['reviews.user', 'reviews' => function($query) {
            $query->select('id', 'user_id', 'product_id', 'rating', 'comment', 'created_at');
        }])
        ->withAvg('reviews', 'rating')
        ->findOrFail($id);

        $averageRating = number_format($product->reviews_avg_rating ?? 0, 1);

        return response()->json([
            'status' => 200,
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
