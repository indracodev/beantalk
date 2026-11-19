<?php

use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\InboxController;
use App\Http\Controllers\Admin\IntegrationController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\TeamController;
use App\Http\Controllers\Admin\VisitorController;
use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Route;

Route::get('/', [LoginController::class, 'root']);

// Autentikasi (email ATAU username)
Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [LoginController::class, 'login'])->middleware('throttle:15,1');
Route::post('logout', [LoginController::class, 'logout'])->name('logout');

// Admin Dashboard (sesi login)
Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard.index');
    Route::get('dashboard/export', [DashboardController::class, 'export'])->name('dashboard.export');

    Route::controller(InboxController::class)->prefix('inbox')->name('inbox')->group(function () {
        Route::get('feed/updates', 'pollUpdates')->name('.updates');
        Route::get('{id?}', 'index')->whereNumber('id');
        Route::post('{id}/reply', 'reply')->whereNumber('id')->name('.reply');
        Route::post('{id}/toggle-bot', 'toggleBot')->whereNumber('id')->name('.toggle-bot');
        Route::get('{id}/messages', 'messages')->whereNumber('id')->name('.messages');
        Route::put('{id}/status', 'updateStatus')->whereNumber('id')->name('.status');
        Route::put('{id}/assign', 'assign')->whereNumber('id')->name('.assign');
        Route::put('{id}/customer', 'updateCustomerName')->whereNumber('id')->name('.customer');
    });

    Route::controller(IntegrationController::class)->prefix('integrations')->name('integrations')->group(function () {
        Route::get('/', 'index');
        Route::get('{id}', 'show')->whereNumber('id')->name('.detail');

        // Mutasi integrasi: superadmin saja (sebelumnya hanya disembunyikan di UI).
        Route::middleware('can:superadmin')->group(function () {
            Route::post('/', 'store')->name('.store');
            Route::post('{id}/regenerate', 'regenerateKey')->whereNumber('id')->name('.regenerate');
            Route::put('{id}/settings', 'updateSettings')->whereNumber('id')->name('.settings');
            Route::post('{id}/test-telegram', 'testTelegram')->whereNumber('id')->name('.test-telegram');
            Route::post('{id}/set-telegram-webhook', 'setTelegramWebhook')->whereNumber('id')->name('.set-telegram-webhook');
        });
    });

    Route::controller(TeamController::class)->group(function () {
        Route::middleware('can:superadmin')->group(function () {
            Route::get('team', 'index')->name('team');
            Route::post('team', 'store')->name('team.store');
            Route::put('team/{id}', 'update')->whereNumber('id')->name('team.update');
            Route::post('team/{id}/impersonate', 'impersonate')->whereNumber('id')->name('team.impersonate');
        });
        // Dapat dipanggil saat sedang menyamar sebagai agent.
        Route::match(['get', 'post'], 'impersonate/leave', 'leaveImpersonation')->name('impersonate.leave');
    });

    Route::get('visitors', [VisitorController::class, 'index'])->name('visitors');
    Route::get('visitors/{id}', [VisitorController::class, 'show'])->whereNumber('id')->name('visitors.detail');
    Route::get('logs', [ActivityLogController::class, 'index'])->name('logs');
    Route::put('profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
});

// Fallback CDN widget bila web server tidak melayani berkas statis secara langsung.
Route::get('{file}.js', [LoginController::class, 'widgetScript'])->whereIn('file', ['chat', 'widget', 'chat-widget']);
