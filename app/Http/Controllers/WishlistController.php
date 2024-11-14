<?php

namespace App\Http\Controllers;

use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{
    /**
     * Menampilkan semua wishlist produk untuk user yang sedang login.
     */
    public function index()
    {
        $wishlists = Wishlist::where('user_id', Auth::id())->with('product')->get();

        return response()->json([
            'status' => 200,
            'message' => 'Berhasil mengambil semua produk dalam wishlist',
            'data' => $wishlists,
        ], 200);
    }


    /**
     * Menambahkan produk ke wishlist.
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $wishlist = Wishlist::create([
            'user_id' => Auth::id(),
            'product_id' => $request->product_id,
        ]);

        return response()->json([
            'status' => 201,
            'message' => 'Produk berhasil ditambahkan ke wishlist',
            'data' => $wishlist,
        ], 201);
    }


    /**
     * Menghapus produk dari wishlist.
     */
    public function destroy($id)
    {
        $wishlist = Wishlist::where('user_id', Auth::id())->where('product_id', $id)->first();

        if (!$wishlist) {
            return response()->json([
                'status' => 404,
                'message' => 'Produk tidak ditemukan dalam wishlist',
            ], 404);
        }

        $wishlist->delete();

        return response()->json([
            'status' => 200,
            'message' => 'Produk berhasil dihapus dari wishlist',
        ], 200);
    }
}
