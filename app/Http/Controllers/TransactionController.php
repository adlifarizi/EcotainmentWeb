<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\JsonResponse;

class TransactionController extends Controller
{
    /**
     * Menampilkan semua transaksi untuk user yang sedang login.
     */
    public function index(): JsonResponse
    {
        $transactions = Transaction::with('items.product')->where('user_id', Auth::id())->get();

        return response()->json([
            'success' => true,
            'message' => 'Berhasil mengambil semua transaksi',
            'data' => $transactions,
        ], 200);
    }

    /**
     * Menyimpan transaksi baru.
     */
    public function store(Request $request): JsonResponse
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
                'status' => 'pending', // Status awal adalah 'pending'
            ]);

            foreach ($request->items as $item) {
                TransactionItem::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                ]);
            }

            $transaction->load('items.product'); // Memuat relasi

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil dibuat',
                'data' => $transaction,
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan pada server',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mengupdate status transaksi berdasarkan ID transaksi.
     */
    public function updateStatus(Request $request, $id): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:pending,processed,completed,canceled',
        ]);

        try {
            $transaction = Transaction::where('user_id', Auth::id())->findOrFail($id);

            // Pastikan status transaksi yang sudah selesai atau dibatalkan tidak bisa diubah
            if ($transaction->status == 'completed' || $transaction->status == 'canceled') {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak dapat mengubah status transaksi yang sudah diselesaikan atau dibatalkan.',
                ], 400);
            }

            $transaction->status = $request->status;
            $transaction->save();

            return response()->json([
                'success' => true,
                'message' => 'Status transaksi berhasil diperbarui',
                'data' => $transaction,
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi tidak ditemukan',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan pada server',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
