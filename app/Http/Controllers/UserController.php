<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\JsonResponse;
use Storage;

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
                'role' => 'nullable|in:user,admin',
            ]);

            if (!$request->email && !$request->phone_number) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email atau nomor telepon harus diisi',
                ], 422);
            }

            // Jika username tidak diisi, ambil bagian awal dari email (sebelum '@')
            $username = $request->username;
            if (!$username && $request->email) {
                $username = explode('@', $request->email)[0];
            }

            $role = $request->role ?? 'user';

            $user = User::create([
                'email' => $request->email,
                'phone_number' => $request->phone_number,
                'password' => Hash::make($request->password),
                'username' => $username,
                'role' => $role,
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
                'profile_picture' => 'nullable|file|image|mimes:jpeg,png,jpg,gif,webp',
            ]);

            $user = Auth::user();

            // Update email
            if ($request->has('email')) {
                $user->email = $request->email;
            }

            // Update password
            if ($request->has('password')) {
                $user->password = Hash::make($request->password);
            }

            // Update username
            if ($request->has('username')) {
                $user->username = $request->username;
            }

            // Update phone_number
            if ($request->has('phone_number')) {
                $user->phone_number = $request->phone_number;
            }

            // Handle profile_picture
            if ($request->hasFile('profile_picture')) {
                // Hapus file gambar lama jika ada
                if ($user->profile_picture) {
                    $oldImagePath = str_replace('/storage', 'public', $user->profile_picture);
                    if (Storage::exists($oldImagePath)) {
                        Storage::delete($oldImagePath);
                    }
                }

                // Simpan gambar baru
                $imagePath = $request->file('profile_picture')->store('images', 'public');
                $user->profile_picture = Storage::url($imagePath);
            }

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
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memperbarui profil',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function updatePassword(Request $request): JsonResponse
    {
        try {
            // Validasi input
            $request->validate([
                'current_password' => 'required|string|min:6',
                'new_password' => 'required|string|min:6',
            ]);

            $user = Auth::user();

            // Memeriksa apakah password lama yang dimasukkan cocok
            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Password lama tidak cocok',
                ], 400);
            }

            // Update password jika password lama cocok
            $user->password = Hash::make($request->new_password);
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Password berhasil diperbarui',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memperbarui password',
                'error' => $e->getMessage(),
            ], 500);
        }
    }



    public function logout(Request $request): JsonResponse
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

    public function getUserData(Request $request): JsonResponse
    {
        try {
            // Mendapatkan data pengguna yang sedang login dan memuat relasi addresses
            $user = $request->user()->load('addresses');

            return response()->json([
                'success' => true,
                'message' => 'Berhasil mendapatkan data pengguna',
                'data' => $user,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data pengguna',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function addAddress(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'recipient_name' => 'required|string',
                'phone_number' => 'required|string',
                'city_or_district' => 'required|string',
                'detailed_address' => 'required|string',
            ]);

            $address = Address::create([
                'user_id' => Auth::id(),
                'recipient_name' => $request->recipient_name,
                'phone_number' => $request->phone_number,
                'province' => $request->province,
                'city_or_district' => $request->city_or_district,
                'detailed_address' => $request->detailed_address,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Alamat berhasil ditambahkan',
                'data' => $address,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data pengguna',
                'error' => $e->getMessage(),
            ], 500);
        }

    }


    public function getAddress(Request $request): JsonResponse
    {
        try {
            // Mendapatkan user yang sedang login
            $user = $request->user();

            // Mendapatkan semua alamat terkait dengan user tersebut
            $addresses = $user->addresses;

            return response()->json([
                'success' => true,
                'message' => 'Berhasil mendapatkan data alamat',
                'data' => $addresses,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data alamat',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function editAddress(Request $request, $addressId): JsonResponse
    {
        try {
            // Validasi data yang dikirimkan
            $request->validate([
                'recipient_name' => 'nullable|string',
                'phone_number' => 'nullable|string',
                'province' => 'nullable|string',
                'city_or_district' => 'nullable|string',
                'detailed_address' => 'nullable|string',
            ]);

            // Mencari alamat berdasarkan ID dan memastikan alamat tersebut milik user yang sedang login
            $address = Address::where('user_id', Auth::id())->where('id', $addressId)->first();

            if (!$address) {
                return response()->json([
                    'success' => false,
                    'message' => 'Alamat tidak ditemukan atau Anda tidak memiliki akses untuk mengedit alamat ini',
                ], 404);
            }

            // Update alamat berdasarkan data yang dikirimkan
            if ($request->has('recipient_name')) {
                $address->recipient_name = $request->recipient_name;
            }

            if ($request->has('phone_number')) {
                $address->phone_number = $request->phone_number;
            }

            if ($request->has('province')) {
                $address->province = $request->province;
            }

            if ($request->has('city_or_district')) {
                $address->city_or_district = $request->city_or_district;
            }

            if ($request->has('detailed_address')) {
                $address->detailed_address = $request->detailed_address;
            }

            // Simpan perubahan
            $address->save();

            return response()->json([
                'success' => true,
                'message' => 'Alamat berhasil diperbarui',
                'data' => $address,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memperbarui alamat',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function deleteAddress(Request $request, $addressId): JsonResponse
    {
        try {
            // Mencari alamat berdasarkan ID dan memastikan alamat tersebut milik user yang sedang login
            $address = Address::where('user_id', Auth::id())->where('id', $addressId)->first();

            if (!$address) {
                return response()->json([
                    'success' => false,
                    'message' => 'Alamat tidak ditemukan atau Anda tidak memiliki akses untuk menghapus alamat ini',
                ], 404);
            }

            // Hapus alamat
            $address->delete();

            return response()->json([
                'success' => true,
                'message' => 'Alamat berhasil dihapus',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus alamat',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

}
