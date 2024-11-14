<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    public function signUp(Request $request)
    {
        try {
            // Validasi input
            $request->validate([
                'email' => 'nullable|email|unique:users,email',
                'phone_number' => 'nullable',
                'password' => 'required|min:6',
                'username' => 'nullable',
            ]);

            // Pastikan setidaknya email atau phone_number diisi
            if (!$request->email && !$request->phone_number) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email atau nomor telepon harus diisi',
                ], 422);
            }

            // Buat user baru
            $user = User::create([
                'email' => $request->email,
                'phone_number' => $request->phone_number,
                'password' => Hash::make($request->password),
                'username' => $request->username,
            ]);

            // Generate token untuk user
            $token = $user->createToken('Ecotainment')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Registrasi berhasil',
                'data' => [
                    'user' => $user,
                    'token' => $token
                ]
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
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function signIn(Request $request)
    {
        try {
            $request->validate([
                'email' => 'nullable|email',
                'phone_number' => 'nullable',
                'password' => 'required|min:6',
            ]);

            // Pastikan setidaknya email atau phone_number diisi
            if (!$request->email && !$request->phone_number) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email atau nomor telepon harus diisi'
                ], 422);
            }

            // Coba login dengan email
            if ($request->email) {
                $credentials = [
                    'email' => $request->email,
                    'password' => $request->password
                ];
            }
            // Coba login dengan phone_number
            else {
                $credentials = [
                    'phone_number' => $request->phone_number,
                    'password' => $request->password
                ];
            }

            if (!Auth::attempt($credentials)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email/No Telepon atau password salah'
                ], 401);
            }

            $user = Auth::user();
            $token = $user->createToken('Ecotainment')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Login berhasil',
                'data' => [
                    'user' => $user,
                    'token' => $token
                ]
            ], 200);

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
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function updateProfile(Request $request)
    {
        try {
            // Validasi data input
            $request->validate([
                'email' => 'nullable|email|unique:users,email,' . Auth::id(),
                'password' => 'nullable|min:6',
                'username' => 'nullable|string',
                'phone_number' => 'nullable|string',
                'profile_picture' => 'nullable|url',
                'address' => 'nullable|string',
            ]);

            // Ambil user yang sedang login
            $user = Auth::user();

            // Update data yang diinputkan
            if ($request->has('email')) {
                $user->email = $request->email;
            }
            if ($request->has('password')) {
                $user->password = Hash::make($request->password);
            }
            if ($request->has('username')) {
                $user->username = $request->username;
            }
            if ($request->has('phone_number')) {
                $user->phone_number = $request->phone_number;
            }
            if ($request->has('profile_picture')) {
                $user->profile_picture = $request->profile_picture;
            }
            if ($request->has('address')) {
                $user->address = $request->address;
            }

            // Simpan perubahan
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Profil berhasil diperbarui',
                'data' => $user
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memperbarui profil',
                'error' => $e->getMessage()
            ], 500);
        }
    }



    public function logout(Request $request)
    {
        try {
            // Cek apakah user terautentikasi dan memiliki token
            if (!$request->user() || !$request->user()->currentAccessToken()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token tidak valid atau sudah tidak aktif'
                ], 401);
            }

            // Revoke token yang sedang digunakan
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'success' => true,
                'message' => 'Berhasil logout'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat proses logout',
                'error' => $e->getMessage(),
                'error_code' => 'AUTH004'
            ], 500);
        }
    }
}