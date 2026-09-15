<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Login user and issue Sanctum token.
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $throttleKey = 'login-attempt:' . $request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return response()->json([
                'success' => false,
                'message' => "Terlalu banyak percobaan login. Silakan coba lagi dalam {$seconds} detik.",
            ], 429);
        }

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            RateLimiter::hit($throttleKey, 60);

            return response()->json([
                'success' => false,
                'message' => 'Email atau kata sandi tidak sesuai.',
            ], 401);
        }

        if ($user->status !== 'aktif') {
            return response()->json([
                'success' => false,
                'message' => 'Akun Anda sedang dinonaktifkan. Silakan hubungi Pengurus Yayasan.',
            ], 403);
        }

        RateLimiter::clear($throttleKey);

        // Revoke older tokens if any to maintain single active session per device
        $token = $user->createToken('simtaq_auth_token')->plainTextToken;

        ActivityLog::log(
            modul: 'Autentikasi',
            aksi: 'LOGIN',
            judul: 'Pengguna berhasil masuk',
            deskripsi: "{$user->name} ({$user->role}) masuk ke dalam sistem.",
            icon: 'login',
            warna_badge: 'positive',
            userId: $user->id
        );

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil. Selamat datang di SIMTAQ Yayasan Al Mukhlisin.',
            'data' => [
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'gender_scope' => $user->gender_scope,
                    'no_hp' => $user->no_hp,
                    'foto' => $user->foto,
                    'status' => $user->status,
                ],
            ],
        ]);
    }

    /**
     * Get currently authenticated user.
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'gender_scope' => $user->gender_scope,
                    'no_hp' => $user->no_hp,
                    'foto' => $user->foto,
                    'status' => $user->status,
                ],
            ],
        ]);
    }

    /**
     * Logout user by revoking current token.
     */
    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->currentAccessToken()->delete();

        ActivityLog::log(
            modul: 'Autentikasi',
            aksi: 'LOGOUT',
            judul: 'Pengguna keluar sistem',
            deskripsi: "{$user->name} keluar dari sistem.",
            icon: 'logout',
            warna_badge: 'info',
            userId: $user->id
        );

        return response()->json([
            'success' => true,
            'message' => 'Berhasil keluar dari sistem.',
        ]);
    }

    /**
     * Update current user profile.
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $request->validate([
            'name' => 'required|string|max:150',
            'no_hp' => 'nullable|string|max:25',
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        $user->name = $request->name;
        $user->no_hp = $request->no_hp;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Profil berhasil diperbarui.',
            'data' => [
                'user' => $user,
            ],
        ]);
    }
}
