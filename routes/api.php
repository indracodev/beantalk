<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| REST API v1 Routes (Universal Customer Chat)
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {

    // ========================================================================
    // 0. AUTHENTICATION ROUTES (Login via Email or Username)
    // ========================================================================
    Route::prefix('auth')->group(function () {
        Route::post('login', 'Api\Auth\AuthController@login');
        Route::post('logout', 'Api\Auth\AuthController@logout');
        Route::get('me', 'Api\Auth\AuthController@me')->middleware(['tenant.scope']);
    });

    // ========================================================================
    // 1. CLIENT PUBLIC ROUTES (Visitor Widget - Protected by X-Project-Key)
    // ========================================================================
    Route::prefix('client')->middleware(['project.key'])->group(function () {
        // Session initialization & widget theme handshake
        Route::post('session/init', 'Api\Client\SessionController@init')->middleware('throttle:100,1');
        Route::post('session/profile', 'Api\Client\SessionController@updateProfile')->middleware('throttle:100,1');

        // Message polling: 300/min per IP (safety net, normal usage ~24 req/min)
        Route::get('conversations/{id}/messages', 'Api\Client\MessageController@index')->middleware('throttle:300,1');

        // Send message: 120/min per IP
        Route::post('conversations/{id}/messages', 'Api\Client\MessageController@store')->middleware('throttle:120,1');

        // Compressed media uploads
        Route::post('conversations/{id}/upload', 'Api\Client\UploadController@upload')->middleware('throttle:100,1');
    });

    // Telegram Bot Webhook Endpoint (Direct incoming updates from Telegram servers)
    Route::post('telegram/webhook/{token_hash?}', 'Api\Client\TelegramWebhookController@handle')->name('api.v1.telegram.webhook');

    // ========================================================================
    // 2. ADMIN & AGENT ROUTES (Protected by Tenant Scope & RBAC)
    // ========================================================================
    Route::prefix('admin')->middleware(['tenant.scope'])->group(function () {
        // Inbox Conversations (Accessible by all tenant staff: owner, admin, agent)
        Route::get('conversations', 'Api\Admin\ConversationController@index');
        Route::get('conversations/{id}', 'Api\Admin\ConversationController@show');
        Route::post('conversations/{id}/reply', 'Api\Admin\ConversationController@reply');

        // Multi-Site Integrations Hub
        Route::get('integrations', 'Api\Admin\IntegrationController@index');
        Route::post('integrations', 'Api\Admin\IntegrationController@store')->middleware('role:superadmin,owner,admin');

        // Team & RBAC Management
        Route::get('team', 'Api\Admin\TeamController@index');
        Route::post('team', 'Api\Admin\TeamController@store')->middleware('role:superadmin,owner,admin');
        Route::put('team/{id}/role', 'Api\Admin\TeamController@updateRole')->middleware('role:superadmin,owner');
        Route::delete('team/{id}', 'Api\Admin\TeamController@destroy')->middleware('role:superadmin,owner');

        // Audit Trail & Activity Logs
        Route::get('activity-logs', 'Api\Admin\ActivityLogController@index');
    });
});
