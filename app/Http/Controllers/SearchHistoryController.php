<?php

namespace App\Http\Controllers;

use App\Models\SearchHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;

class SearchHistoryController extends Controller
{
    /**
     * Menampilkan semua riwayat pencarian untuk user yang sedang login.
     */
    public function index(): JsonResponse
    {
        $searchHistory = SearchHistory::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')  // Mengurutkan berdasarkan waktu terbaru
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Berhasil mengambil riwayat pencarian',
            'data' => $searchHistory,
        ], 200);
    }

    /**
     * Menambahkan riwayat pencarian baru.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'search_query' => 'required|string|max:255',
            ]);

            // Cek apakah pencarian sudah ada
            $existingSearch = SearchHistory::where('user_id', Auth::id())
                ->where('search_query', $request->search_query)
                ->first();

            // Jika sudah ada, tidak melakukan apa-apa
            if ($existingSearch) {
                return response()->json([
                    'success' => true,
                    'message' => 'Riwayat pencarian sudah ada, tidak ada perubahan',
                ], 200);
            }

            // Jika belum ada, tambahkan pencarian baru
            $searchHistory = SearchHistory::create([
                'user_id' => Auth::id(),
                'search_query' => $request->search_query,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Riwayat pencarian berhasil ditambahkan',
                'data' => $searchHistory,
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
