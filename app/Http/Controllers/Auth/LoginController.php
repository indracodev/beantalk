<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class LoginController extends Controller
{
    /**
     * Show the login form.
     * GET /login
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('admin.inbox');
        }

        return view('auth.login');
    }

    /**
     * Handle an authentication attempt using Email OR Username.
     * POST /login
     */
    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'login'    => 'required|string',
            'password' => 'required|string',
        ], [
            'login.required'    => 'Email atau username wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $loginInput = trim($request->input('login'));
        $password   = $request->input('password');

        // Deteksi apakah input berupa alamat email atau username
        $isEmail = filter_var($loginInput, FILTER_VALIDATE_EMAIL);
        $field   = $isEmail ? 'email' : 'username';

        // Cari user berdasarkan field yang sesuai
        $user = User::where($field, $loginInput)->first();

        // Fallback: Jika tidak ditemukan via username, coba cari via email
        if (!$user && !$isEmail) {
            $user = User::where('email', $loginInput)->first();
        }

        if (!$user || !Hash::check($password, $user->password)) {
            return back()->withInput($request->only('login', 'remember'))->withErrors([
                'login' => 'Email/username atau password yang Anda masukkan salah.',
            ]);
        }

        // Login ke session
        Auth::login($user, $request->boolean('remember'));

        if ($user->status === 'offline') {
            $user->update(['status' => 'online']);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('admin.inbox'));
    }

    /**
     * Log the user out of the application.
     * POST /logout
     */
    public function logout(Request $request): RedirectResponse
    {
        $user = Auth::user();
        if ($user) {
            $user->update(['status' => 'offline']);
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with('success', 'Anda telah berhasil keluar.');
    }
}
