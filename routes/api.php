<?php

use App\Http\Controllers\Api\Admin\ActivityLogController;
use App\Http\Controllers\Api\Admin\ConversationController;
use App\Http\Controllers\Api\Admin\IntegrationController;
use App\Http\Controllers\Api\Admin\TeamController;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Client\MessageController;
use App\Http\Controllers\Api\Client\SessionController;
use App\Http\Controllers\Api\Client\TelegramWebhookController;
use App\Http\Controllers\Api\Client\UploadController;
use Illuminate\Support\Facades\Route;

/*
| REST API v1 — Universal Customer Chat. Throttle diatur per-route (tidak ada throttle global).
*/
Route::prefix('v1')->group(function () {

    // 0. Autentikasi staf
    Route::prefix('auth')->controller(AuthController::class)->group(function () {
        Route::post('login', 'login')->middleware('throttle:15,1');
        Route::post('logout', 'logout');
        Route::get('me', 'me')->middleware('tenant.scope');
    });

    // 1. Widget pengunjung (dilindungi X-Project-Key + whitelist domain)
    Route::prefix('client')->middleware('project.key')->group(function () {
        Route::post('session/init', [SessionController::class, 'init'])->middleware('throttle:100,1');
        Route::post('session/profile', [SessionController::class, 'updateProfile'])->middleware('throttle:100,1');

        Route::controller(MessageController::class)->prefix('conversations')->group(function () {
            Route::get('/', 'listConversations')->middleware('throttle:100,1');
            Route::post('{id}/resolve', 'resolve')->middleware('throttle:60,1');
            // Polling: 300/menit per IP (pemakaian normal ±24/menit).
            Route::get('{id}/messages', 'index')->middleware('throttle:300,1');
            Route::post('{id}/messages', 'store')->middleware('throttle:120,1');
        });

        Route::post('conversations/{id}/upload', [UploadController::class, 'upload'])->middleware('throttle:30,1');
    });

    // Webhook Telegram (diverifikasi via header secret token)
    Route::post('telegram/webhook/{token_hash?}', [TelegramWebhookController::class, 'handle'])
        ->middleware('throttle:120,1')
        ->name('api.v1.telegram.webhook');

    // 2. Admin & agent (tenant scope + RBAC)
    Route::prefix('admin')->middleware('tenant.scope')->group(function () {
        Route::controller(ConversationController::class)->prefix('conversations')->group(function () {
            Route::get('/', 'index');
            Route::get('{id}', 'show');
            Route::post('{id}/reply', 'reply');
            Route::post('{id}/close', 'close');
            Route::post('{id}/email-transcript', 'emailTranscript');
        });

        Route::get('integrations', [IntegrationController::class, 'index']);
        Route::post('integrations', [IntegrationController::class, 'store'])->middleware('role:superadmin');

        Route::controller(TeamController::class)->prefix('team')->group(function () {
            Route::get('/', 'index');
            Route::post('/', 'store')->middleware('role:superadmin');
            Route::put('{id}/role', 'updateRole')->middleware('role:superadmin');
            Route::delete('{id}', 'destroy')->middleware('role:superadmin');
        });

        Route::get('activity-logs', [ActivityLogController::class, 'index']);
    });
});
