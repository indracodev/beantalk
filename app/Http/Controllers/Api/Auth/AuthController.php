<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Authenticate staff/agent via Email OR Username.
     * POST /api/v1/auth/login
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'login'    => 'required_without_all:username,email|string',
            'username' => 'nullable|string',
            'email'    => 'nullable|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code'    => 'VALIDATION_ERROR',
                    'message' => 'Silakan masukkan email/username dan password.',
                    'details' => $validator->errors()
                ]
            ], 422);
        }

        // Resolusi field login: prioritas 'login', lalu 'username', lalu 'email'
        $loginInput = trim($request->input('login') ?? $request->input('username') ?? $request->input('email'));
        $password   = $request->input('password');

        // Deteksi apakah input berupa alamat email atau username
        $isEmail = filter_var($loginInput, FILTER_VALIDATE_EMAIL);
        $field   = $isEmail ? 'email' : 'username';

        // Cari user berdasarkan email atau username
        $user = User::where($field, $loginInput)->first();

        // Fallback: Jika tidak ketemu via username, coba cari via email juga
        if (!$user && !$isEmail) {
            $user = User::where('email', $loginInput)->first();
        }

        if (!$user || !Hash::check($password, $user->password)) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code'    => 'INVALID_CREDENTIALS',
                    'message' => 'Email/username atau password tidak valid.'
                ]
            ], 401);
        }

        // Login ke session guard jika request berbasis web
        Auth::login($user, $request->boolean('remember'));

        // Update status user ke online jika sedang offline
        if ($user->status === 'offline') {
            $user->update(['status' => 'online']);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id'         => $user->id,
                    'tenant_id'  => $user->tenant_id,
                    'name'       => $user->name,
                    'username'   => $user->username,
                    'email'      => $user->email,
                    'role'       => $user->role,
                    'status'     => $user->status,
                    'avatar_url' => $user->avatar_url,
                ],
                'message' => 'Login berhasil.'
            ]
        ]);
    }

    /**
     * Get current authenticated user profile
     * GET /api/v1/auth/me
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code'    => 'UNAUTHORIZED',
                    'message' => 'Sesi login tidak ditemukan.'
                ]
            ], 401);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id'         => $user->id,
                    'tenant_id'  => $user->tenant_id,
                    'name'       => $user->name,
                    'username'   => $user->username,
                    'email'      => $user->email,
                    'role'       => $user->role,
                    'status'     => $user->status,
                    'avatar_url' => $user->avatar_url,
                ]
            ]
        ]);
    }

    /**
     * Logout current user
     * POST /api/v1/auth/logout
     */
    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user) {
            $user->update(['status' => 'offline']);
        }

        Auth::logout();

        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return response()->json([
            'success' => true,
            'data' => [
                'message' => 'Logout berhasil.'
            ]
        ]);
    }
}
