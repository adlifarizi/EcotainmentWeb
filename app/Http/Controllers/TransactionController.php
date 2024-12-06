<?php

namespace App\Http\Controllers;

use App\Models\PurchaseHistory;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\JsonResponse;
use Storage;

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
                'total_amount' => intval($request->total_amount),
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
            'status' => 'required|in:pending,waiting_for_confirmation,processed,completed,canceled',
        ]);

        try {
            $transaction = Transaction::with('items.product')->where('user_id', Auth::id())->findOrFail($id);

            // Pastikan status transaksi yang sudah selesai atau dibatalkan tidak bisa diubah
            if ($transaction->status == 'completed' || $transaction->status == 'canceled') {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak dapat mengubah status transaksi yang sudah diselesaikan atau dibatalkan.',
                ], 400);
            }

            // Jika status diubah menjadi "completed"
            if ($request->status === 'completed') {
                foreach ($transaction->items as $item) {
                    // Tambahkan jumlah quantity ke total_sales produk terkait
                    $product = $item->product;
                    if ($product) {
                        $product->increment('total_sales', $item->quantity);
                    }
                }

                // Tambahkan riwayat pembelian
                PurchaseHistory::create([
                    'user_id' => Auth::id(),
                    'transaction_id' => $transaction->id,
                ]);
            }

            // Update status transaksi
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



    /**
     * Mengunggah bukti pembayaran untuk transaksi berdasarkan ID transaksi.
     */
    public function uploadPaymentProof(Request $request, $id): JsonResponse
    {
        $request->validate([
            'payment_proof' => 'required|file|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        try {
            // Cari transaksi berdasarkan ID dan user yang sedang login
            $transaction = Transaction::where('user_id', Auth::id())->findOrFail($id);

            // Pastikan transaksi masih dalam status pending
            if ($transaction->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Bukti pembayaran hanya dapat diunggah untuk transaksi dengan status pending.',
                ], 400);
            }

            // Hapus file bukti pembayaran lama jika ada
            if ($transaction->payment_proof) {
                $oldImagePath = str_replace('/storage', 'public', $transaction->payment_proof);
                if (Storage::exists($oldImagePath)) {
                    Storage::delete($oldImagePath);
                }
            }

            // Simpan file bukti pembayaran baru
            $imagePath = $request->file('payment_proof')->store('payment_proofs', 'public');

            // Perbarui path bukti pembayaran di database
            $transaction->payment_proof = Storage::url($imagePath);

            // Ubah status transaksi menjadi processed
            $transaction->status = 'waiting_for_confirmation';

            $transaction->save();

            return response()->json([
                'success' => true,
                'message' => 'Bukti pembayaran berhasil diunggah.',
                'data' => [
                    'transaction_id' => $transaction->id,
                    'payment_proof_url' => $transaction->payment_proof,
                ],
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi tidak ditemukan.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan pada server.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Menampilkan semua transaksi tanpa batasan user (untuk admin).
     */
    public function getAllTransactions(): JsonResponse
    {
        try {
            // Mengambil semua transaksi dengan relasi item dan produk
            $transactions = Transaction::with('items.product', 'user')->get();

            return response()->json([
                'success' => true,
                'message' => 'Berhasil mengambil semua transaksi',
                'data' => $transactions,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan pada server',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mengupdate status transaksi berdasarkan ID transaksi (untuk admin).
     */
    public function adminUpdateTransactionStatus(Request $request, $id): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:pending,waiting_for_confirmation,processed,completed,canceled',
        ]);

        try {
            // Cari transaksi berdasarkan ID
            $transaction = Transaction::findOrFail($id);

            // Perbarui status transaksi
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
