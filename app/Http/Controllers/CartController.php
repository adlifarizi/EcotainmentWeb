<?php

namespace App\Http\Controllers;
    
use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    /**
     * Menampilkan semua item dalam cart untuk user yang sedang login.
     */
    public function index()
    {
        $carts = Cart::where('user_id', Auth::id())->with('product')->get();

        return response()->json([
            'status' => 200,
            'message' => 'Berhasil mengambil semua item dalam cart',
            'data' => $carts,
        ], 200);
    }

    /**
     * Menambahkan produk ke cart atau memperbarui jumlah jika produk sudah ada.
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $cart = Cart::updateOrCreate(
            [
                'user_id' => Auth::id(),
                'product_id' => $request->product_id,
            ],
            [
                'quantity' => $request->quantity,
            ]
        );

        return response()->json([
            'status' => 201,
            'message' => 'Produk berhasil ditambahkan atau diperbarui di cart',
            'data' => $cart,
        ], 201);
    }

    /**
     * Menghapus produk dari cart.
     */
    public function destroy($id)
    {
        $cart = Cart::where('user_id', Auth::id())->where('product_id', $id)->first();

        if (!$cart) {
            return response()->json([
                'status' => 404,
                'message' => 'Produk tidak ditemukan dalam cart',
            ], 404);
        }

        $cart->delete();

        return response()->json([
            'status' => 200,
            'message' => 'Produk berhasil dihapus dari cart',
        ], 200);
    }
}
