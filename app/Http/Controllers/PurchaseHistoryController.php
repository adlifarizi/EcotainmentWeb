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
            ->with(['transaction.items.product']) // Memuat detail transaksi dan produk
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Berhasil mengambil riwayat pembelian',
            'data' => $purchaseHistory,
        ], 200);
    }

}
