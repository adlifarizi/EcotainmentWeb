<?php

namespace App\Http\Controllers;

use App\Models\PurchaseHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;

class PurchaseHistoryController extends Controller
{
    /**
     * Menampilkan semua riwayat pembelian untuk user yang sedang login.
     */
    public function index(): JsonResponse
    {
        $purchaseHistory = PurchaseHistory::where('user_id', Auth::id())
            ->with('product') // Asumsi relasi produk sudah ada
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Berhasil mengambil riwayat pembelian',
            'data' => $purchaseHistory,
        ], 200);
    }

    /**
     * Menambahkan riwayat pembelian baru.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'total_price' => 'required|numeric|min:0',
        ]);

        try {
            $purchaseHistory = PurchaseHistory::create([
                'user_id' => Auth::id(),
                'product_id' => $request->product_id,
                'quantity' => $request->quantity,
                'total_price' => $request->total_price,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Riwayat pembelian berhasil ditambahkan',
                'data' => $purchaseHistory,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan pada server',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
