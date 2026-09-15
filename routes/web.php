<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('admin.dashboard');
    }
    return redirect('/login');
});

// Authentication Web Routes (Login via Email or Username)
Route::get('login', 'Auth\LoginController@showLoginForm')->name('login');
Route::post('login', 'Auth\LoginController@login');
Route::post('logout', 'Auth\LoginController@logout')->name('logout');

// Admin Dashboard Web Routes (Protected by Auth Session)
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', 'Admin\DashboardController@index')->name('dashboard');
    Route::get('dashboard', 'Admin\DashboardController@index')->name('dashboard.index');
    Route::get('dashboard/export', 'Admin\DashboardController@exportReport')->name('dashboard.export');
    Route::get('inbox/feed/updates', 'Admin\DashboardController@pollUpdates')->name('inbox.updates');
    Route::get('inbox/{id?}', 'Admin\DashboardController@inbox')->name('inbox');
    Route::post('inbox/{id}/reply', 'Admin\DashboardController@reply')->name('inbox.reply');
    Route::post('inbox/{id}/toggle-bot', 'Admin\DashboardController@toggleBot')->name('inbox.toggle-bot');
    Route::get('inbox/{id}/messages', 'Admin\DashboardController@messages')->name('inbox.messages');
    Route::put('inbox/{id}/status', 'Admin\DashboardController@updateStatus')->name('inbox.status');
    Route::put('inbox/{id}/assign', 'Admin\DashboardController@assign')->name('inbox.assign');
    Route::put('inbox/{id}/customer', 'Admin\DashboardController@updateCustomerName')->name('inbox.customer');

    Route::get('integrations', 'Admin\DashboardController@integrations')->name('integrations');
    Route::post('integrations', 'Admin\DashboardController@storeIntegration')->name('integrations.store');
    Route::get('integrations/{id}', 'Admin\DashboardController@integrationDetail')->name('integrations.detail');
    Route::post('integrations/{id}/regenerate', 'Admin\DashboardController@regenerateKey')->name('integrations.regenerate');
    Route::put('integrations/{id}/settings', 'Admin\DashboardController@updateWidgetSettings')->name('integrations.settings');
    Route::post('integrations/{id}/test-telegram', 'Admin\DashboardController@testTelegramAlert')->name('integrations.test-telegram');
    Route::post('integrations/{id}/set-telegram-webhook', 'Admin\DashboardController@setTelegramWebhook')->name('integrations.set-telegram-webhook');

    Route::get('team', 'Admin\DashboardController@team')->name('team');
    Route::post('team', 'Admin\DashboardController@storeTeam')->name('team.store');
    Route::post('team/{id}/impersonate', 'Admin\DashboardController@impersonate')->name('team.impersonate');
    Route::match(['get', 'post'], 'impersonate/leave', 'Admin\DashboardController@leaveImpersonation')->name('impersonate.leave');

    Route::get('logs', 'Admin\DashboardController@logs')->name('logs');
});

// Public CDN Endpoint for Embed Widget (Fallback if not directly intercepted by web server)
Route::get('{file}.js', function ($file) {
    $target = public_path($file . '.js');
    if (!file_exists($target)) {
        $target = public_path('chat-widget.js');
    }
    if (file_exists($target)) {
        return response()->file($target, [
            'Content-Type' => 'application/javascript; charset=utf-8',
            'Access-Control-Allow-Origin' => '*',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }
    abort(404);
})->where('file', 'chat|widget|chat-widget');


