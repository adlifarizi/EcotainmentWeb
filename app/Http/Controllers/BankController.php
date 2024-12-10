<?php

namespace App\Http\Controllers;

use App\Models\Bank;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BankController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $banks = Bank::all();

        return response()->json([
            'success' => true,
            'message' => 'Berhasil mengambil data bank',
            'data' => $banks,
        ], 200);
    }



    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'logo' => 'nullable|file|image|mimes:jpeg,png,jpg,gif,webp',
            'account_number' => 'required|string|max:50',
            'account_holder' => 'required|string|max:255',
            'payment_instructions' => 'required|string',
        ]);

        $logoUrl = null;
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('logos', 'public');
            $logoUrl = Storage::url($logoPath);
        }

        $bank = Bank::create([
            'name' => $request->name,
            'logo' => $logoUrl,
            'account_number' => $request->account_number,
            'account_holder' => $request->account_holder,
            'payment_instructions' => $request->payment_instructions,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Bank berhasil ditambahkan',
            'data' => $bank,
        ], 201);
    }


    public function show($bankId): JsonResponse
    {
        try {
            $bank = Bank::findOrFail($bankId);

            return response()->json([
                'success' => true,
                'message' => 'Berhasil mengambil detail bank',
                'data' => $bank,
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Bank tidak ditemukan',
            ], 404);
        }
    }



    public function update(Request $request, $bankId): JsonResponse
    {
        try {
            $bank = Bank::findOrFail($bankId);

            $request->validate([
                'name' => 'nullable|string|max:255',
                'logo' => 'nullable|file|image|mimes:jpeg,png,jpg,gif,webp',
                'account_number' => 'nullable|string|max:50',
                'account_holder' => 'nullable|string|max:255',
                'payment_instructions' => 'nullable|string',
            ]);

            if ($request->has('name')) {
                $bank->name = $request->name;
            }
            if ($request->has('account_number')) {
                $bank->account_number = $request->account_number;
            }
            if ($request->has('account_holder')) {
                $bank->account_holder = $request->account_holder;
            }
            if ($request->has('payment_instructions')) {
                $bank->payment_instructions = $request->payment_instructions;
            }

            if ($request->hasFile('logo')) {
                if ($bank->logo) {
                    Storage::delete('public/' . str_replace('/storage/', '', $bank->logo));
                }
                $logoPath = $request->file('logo')->store('logos', 'public');
                $bank->logo = Storage::url($logoPath);
            }

            $bank->save();

            return response()->json([
                'success' => true,
                'message' => 'Bank berhasil diperbarui',
                'data' => $bank,
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Bank tidak ditemukan',
            ], 404);
        }
    }



    public function destroy($bankId): JsonResponse
    {
        try {
            $bank = Bank::findOrFail($bankId);

            if ($bank->logo) {
                Storage::delete('public/' . str_replace('/storage/', '', $bank->logo));
            }

            $bank->delete();

            return response()->json([
                'success' => true,
                'message' => 'Bank berhasil dihapus',
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Bank tidak ditemukan',
            ], 404);
        }
    }
}
