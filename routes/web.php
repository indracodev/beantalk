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
        return redirect()->route('admin.inbox');
    }
    return redirect('/login');
});

// Authentication Web Routes (Login via Email or Username)
Route::get('login', 'Auth\LoginController@showLoginForm')->name('login');
Route::post('login', 'Auth\LoginController@login');
Route::post('logout', 'Auth\LoginController@logout')->name('logout');

// Admin Dashboard Web Routes (Protected by Auth Session)
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', 'Admin\DashboardController@inbox')->name('dashboard');
    Route::get('inbox/{id?}', 'Admin\DashboardController@inbox')->name('inbox');
    Route::post('inbox/{id}/reply', 'Admin\DashboardController@reply')->name('inbox.reply');
    Route::get('inbox/{id}/messages', 'Admin\DashboardController@messages')->name('inbox.messages');
    Route::put('inbox/{id}/status', 'Admin\DashboardController@updateStatus')->name('inbox.status');
    Route::put('inbox/{id}/assign', 'Admin\DashboardController@assign')->name('inbox.assign');

    Route::get('integrations', 'Admin\DashboardController@integrations')->name('integrations');
    Route::post('integrations', 'Admin\DashboardController@storeIntegration')->name('integrations.store');

    Route::get('team', 'Admin\DashboardController@team')->name('team');
    Route::post('team', 'Admin\DashboardController@storeTeam')->name('team.store');

    Route::get('logs', 'Admin\DashboardController@logs')->name('logs');
});

