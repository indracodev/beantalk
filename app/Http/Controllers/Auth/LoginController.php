<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class LoginController extends Controller
{
    /** GET / */
    public function root(): RedirectResponse
    {
        return Auth::check() ? to_route('admin.dashboard') : redirect('/login');
    }

    /** GET /{chat|widget|chat-widget}.js — fallback CDN widget. */
    public function widgetScript(string $file): BinaryFileResponse
    {
        $path = collect([public_path("{$file}.js"), public_path('chat-widget.js')])->first(fn ($p) => is_file($p));
        abort_unless($path, 404);

        return response()->file($path, [
            'Content-Type' => 'application/javascript; charset=utf-8',
            'Access-Control-Allow-Origin' => '*',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    /** GET /login */
    public function showLoginForm(): View|RedirectResponse
    {
        return Auth::check() ? to_route('admin.inbox') : view('auth.login');
    }

    /** POST /login — email ATAU username. */
    public function login(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'login' => 'required|string|max:255',
            'password' => 'required|string',
        ], [
            'login.required' => 'Email atau username wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $user = User::findByLogin($data['login']);
        if (! $user || ! Hash::check($data['password'], $user->password)) {
            return back()->withInput($request->only('login', 'remember'))
                ->withErrors(['login' => 'Email/username atau password yang Anda masukkan salah.']);
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        if ($user->status === 'offline') {
            $user->update(['status' => 'online']);
        }

        return redirect()->intended(route('admin.inbox'));
    }

    /** POST /logout */
    public function logout(Request $request): RedirectResponse
    {
        $request->user()?->update(['status' => 'offline']);

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with('success', 'Anda telah berhasil keluar.');
    }
}
