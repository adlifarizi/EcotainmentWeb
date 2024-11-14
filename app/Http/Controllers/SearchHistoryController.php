<?php

namespace App\Http\Controllers;

use App\Models\SearchHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SearchHistoryController extends Controller
{
    /**
     * Menampilkan semua riwayat pencarian untuk user yang sedang login.
     */
    public function index()
    {
        $searchHistory = SearchHistory::where('user_id', Auth::id())->get();

        return response()->json([
            'status' => 200,
            'message' => 'Berhasil mengambil riwayat pencarian',
            'data' => $searchHistory,
        ], 200);
    }

    /**
     * Menambahkan riwayat pencarian baru.
     */
    public function store(Request $request)
    {
        $request->validate([
            'search_query' => 'required|string|max:255',
        ]);

        $searchHistory = SearchHistory::create([
            'user_id' => Auth::id(),
            'search_query' => $request->search_query,
        ]);

        return response()->json([
            'status' => 201,
            'message' => 'Riwayat pencarian berhasil ditambahkan',
            'data' => $searchHistory,
        ], 201);
    }
}
