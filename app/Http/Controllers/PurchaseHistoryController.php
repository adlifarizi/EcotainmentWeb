<?php

namespace App\Http\Controllers;

use App\Models\PurchaseHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PurchaseHistoryController extends Controller
{
     /**
     * Menampilkan semua riwayat pembelian untuk user yang sedang login.
     */
    public function index()
    {
        $purchaseHistory = PurchaseHistory::where('user_id', operator: Auth::id())->with('product')->get();

        return response()->json([
            'status' => 200,
            'message' => 'Berhasil mengambil riwayat pembelian',
            'data' => $purchaseHistory,
        ], 200);
    }

    /**
     * Menambahkan riwayat pembelian baru.
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'total_price' => 'required|numeric|min:0',
        ]);

        $purchaseHistory = PurchaseHistory::create([
            'user_id' => Auth::id(),
            'product_id' => $request->product_id,
            'quantity' => $request->quantity,
            'total_price' => $request->total_price,
        ]);

        return response()->json([
            'status' => 201,
            'message' => 'Riwayat pembelian berhasil ditambahkan',
            'data' => $purchaseHistory,
        ], 201);
    }
}
