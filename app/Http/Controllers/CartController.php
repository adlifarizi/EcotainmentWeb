<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    /**
     * Menampilkan semua item dalam cart untuk user yang sedang login.
     */
    public function index(): JsonResponse
    {
        $carts = Cart::where('user_id', Auth::id())->with('product')->get();

        return response()->json([
            'success' => true,
            'message' => 'Berhasil mengambil semua item dalam cart',
            'data' => $carts,
        ], 200);
    }

    /**
     * Menambahkan produk ke cart atau memperbarui jumlah jika produk sudah ada.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'product_id' => 'required|exists:products,id',
                'quantity' => 'required|integer|min:1',
            ]);

            // Periksa apakah produk tersedia
            $product = Product::find($request->product_id);
            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Produk tidak ditemukan',
                ], 404);
            }

            // Tambahkan atau perbarui item ke cart
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
                'success' => true,
                'message' => 'Produk berhasil ditambahkan atau diperbarui di cart',
                'data' => $cart,
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Tangkap kesalahan validasi
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors(), // Menampilkan pesan error validasi
            ], 422);

        } catch (\Exception $e) {
            // Tangkap kesalahan tak terduga
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan pada server',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    /**
     * Menghapus produk dari cart.
     */
    public function destroy($id): JsonResponse
    {
        $cart = Cart::where('user_id', Auth::id())->where('product_id', $id)->first();

        if (!$cart) {
            return response()->json([
                'success' => false,
                'message' => 'Produk tidak ditemukan dalam cart',
            ], 404);
        }

        $cart->delete();

        return response()->json([
            'success' => true,
            'message' => 'Produk berhasil dihapus dari cart',
        ], 200);
    }

    /**
     * Menambah atau mengurangi kuantitas produk di cart.
     */
    public function updateQuantity(Request $request, $id): JsonResponse
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $cart = Cart::where('user_id', Auth::id())->where('product_id', $id)->first();

        if (!$cart) {
            return response()->json([
                'success' => false,
                'message' => 'Produk tidak ditemukan dalam cart',
            ], 404);
        }

        // Update quantity
        $cart->quantity = $request->quantity;
        $cart->save();

        return response()->json([
            'success' => true,
            'message' => 'Kuantitas produk berhasil diperbarui',
            'data' => $cart,
        ], 200);
    }
}