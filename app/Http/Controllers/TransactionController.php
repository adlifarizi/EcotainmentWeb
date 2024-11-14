<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class TransactionController extends Controller
{
    /**
     * Menampilkan semua transaksi untuk user yang sedang login.
     */
    public function index()
    {
        $transactions = Transaction::with('items.product')->where('user_id', Auth::id())->get();

        return response()->json([
            'status' => 200,
            'message' => 'Berhasil mengambil semua transaksi',
            'data' => $transactions,
        ], 200);
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'total_amount' => 'required|numeric',
                'items' => 'required|array',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.quantity' => 'required|integer|min:1',
            ]);

            // Proses transaksi jika validasi lolos
            $transaction = Transaction::create([
                'user_id' => Auth::id(),
                'total_amount' => $request->total_amount,
                'status' => 'pending',
            ]);

            foreach ($request->items as $item) {
                TransactionItem::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                ]);
            }

            return response()->json([
                'status' => 201,
                'message' => 'Transaksi berhasil dibuat',
                'data' => $transaction->load('items.product'),
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'status' => 422,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        }
    }


    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,completed,canceled',
        ]);

        $transaction = Transaction::where('user_id', Auth::id())->where('id', $id)->first();

        if (!$transaction) {
            return response()->json([
                'status' => 404,
                'message' => 'Transaksi tidak ditemukan',
            ], 404);
        }

        $transaction->status = $request->status;
        $transaction->save();

        return response()->json([
            'status' => 200,
            'message' => 'Status transaksi berhasil diperbarui',
            'data' => $transaction,
        ], 200);
    }
}
