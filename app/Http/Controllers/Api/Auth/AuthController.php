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
    /** POST /api/v1/auth/login — field 'login' (atau 'username'/'email') + password. */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'login' => 'required_without_all:username,email|nullable|string|max:255',
            'username' => 'nullable|string|max:255',
            'email' => 'nullable|string|max:255',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->fail('VALIDATION_ERROR', 'Silakan masukkan email/username dan password.', 422, ['details' => $validator->errors()]);
        }

        $user = User::findByLogin((string) ($request->input('login') ?? $request->input('username') ?? $request->input('email')));
        if (! $user || ! Hash::check($request->input('password'), $user->password)) {
            return $this->fail('INVALID_CREDENTIALS', 'Email/username atau password tidak valid.', 401);
        }

        Auth::login($user, $request->boolean('remember'));
        if ($user->status === 'offline') {
            $user->update(['status' => 'online']);
        }

        return $this->ok(['user' => $user->toProfileArray(), 'message' => 'Login berhasil.']);
    }

    /** GET /api/v1/auth/me */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        return $user
            ? $this->ok(['user' => $user->toProfileArray()])
            : $this->fail('UNAUTHORIZED', 'Sesi login tidak ditemukan.', 401);
    }

    /** POST /api/v1/auth/logout */
    public function logout(Request $request): JsonResponse
    {
        $request->user()?->update(['status' => 'offline']);
        Auth::logout();

        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return $this->ok(['message' => 'Logout berhasil.']);
    }
}
