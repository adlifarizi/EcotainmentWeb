<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{
    /**
     * Menampilkan semua wishlist produk untuk user yang sedang login.
     */
    public function index(): JsonResponse
    {
        $wishlists = Wishlist::where('user_id', Auth::id())
            ->with('product')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Berhasil mengambil semua produk dalam wishlist',
            'data' => $wishlists,
        ], 200);
    }

    /**
     * Menambahkan atau menghapus produk dari wishlist.
     */
    public function toggle(Request $request): JsonResponse
    {
        // Validasi inputan product_id dan pastikan produk ada di database
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        // Cek apakah produk ada di database
        $productExists = Product::find($request->product_id);

        if (!$productExists) {
            return response()->json([
                'success' => false,
                'message' => 'Produk tidak ditemukan di database',
            ], 404);
        }

        // Cek apakah produk sudah ada di wishlist
        $wishlist = Wishlist::where('user_id', Auth::id())
            ->where('product_id', $request->product_id)
            ->first();

        if ($wishlist) {
            // Hapus produk jika sudah ada di wishlist
            $wishlist->delete();

            return response()->json([
                'success' => true,
                'message' => 'Produk berhasil dihapus dari wishlist',
            ], 200);
        } else {
            // Tambahkan produk jika belum ada di wishlist
            $newWishlist = Wishlist::create([
                'user_id' => Auth::id(),
                'product_id' => $request->product_id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Produk berhasil ditambahkan ke wishlist',
                'data' => $newWishlist,
            ], 201);
        }
    }

}
