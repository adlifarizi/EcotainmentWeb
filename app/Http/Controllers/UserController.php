<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    public function signUp(Request $request): JsonResponse 
    {
        try {
            $request->validate([
                'email' => 'nullable|email|unique:users,email',
                'phone_number' => 'nullable',
                'password' => 'required|min:6',
                'username' => 'nullable',
            ]);

            if (!$request->email && !$request->phone_number) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email atau nomor telepon harus diisi',
                ], 422);
            }

            $user = User::create([
                'email' => $request->email,
                'phone_number' => $request->phone_number,
                'password' => Hash::make($request->password),
                'username' => $request->username,
            ]);

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

    public function signIn(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'email' => 'nullable|email',
                'phone_number' => 'nullable',
                'password' => 'required|min:6',
            ]);

            if (!$request->email && !$request->phone_number) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email atau nomor telepon harus diisi',
                ], 422);
            }

            $credentials = $request->email
                ? ['email' => $request->email, 'password' => $request->password]
                : ['phone_number' => $request->phone_number, 'password' => $request->password];

            if (!Auth::attempt($credentials)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email/No Telepon atau password salah',
                ], 401);
            }

            $user = Auth::user();
            $token = $user->createToken('Ecotainment')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Login berhasil',
                'data' => [
                    'user' => $user,
                    'token' => $token,
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

    public function updateProfile(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'email' => 'nullable|email|unique:users,email,' . Auth::id(),
                'password' => 'nullable|min:6',
                'username' => 'nullable|string',
                'phone_number' => 'nullable|string',
                'profile_picture' => 'nullable|url',
                'address' => 'nullable|string',
            ]);

            $user = Auth::user();

            if ($request->has('email')) $user->email = $request->email;
            if ($request->has('password')) $user->password = Hash::make($request->password);
            if ($request->has('username')) $user->username = $request->username;
            if ($request->has('phone_number')) $user->phone_number = $request->phone_number;
            if ($request->has('profile_picture')) $user->profile_picture = $request->profile_picture;
            if ($request->has('address')) $user->address = $request->address;

            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Profil berhasil diperbarui',
                'data' => $user,
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

    public function logout(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            if (!$request->user() || !$request->user()->currentAccessToken()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token tidak valid atau sudah tidak aktif'
                ], 401);
            }

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
            ], 500);
        }
    }
}
