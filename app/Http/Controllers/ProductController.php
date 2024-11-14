<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::all();

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
            'image' => 'nullable|string',
        ]);

        // Simpan data produk ke database
        $product = Product::create([
            'name' => $request->name,
            'price' => $request->price,
            'category' => $request->category,
            'description' => $request->description,
            'image' => $request->image,
            'total_sales' => 0, // total_sales diset default ke 0 saat produk baru ditambahkan
        ]);

        return response()->json([
            'status' => 201,
            'message' => 'Produk berhasil ditambahkan',
            'data' => $product,
        ], 201);
    }

}
