<?php

namespace App\Providers;

use App\Models\Conversation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Share unread conversations count to admin sidebar
        View::composer('layouts.partials.sidebar', function ($view) {
            $count = 0;
            if (Auth::check()) {
                $count = Conversation::where('tenant_id', Auth::user()->tenant_id)
                    ->where('unread_agent_count', '>', 0)
                    ->count();
            }
            $view->with('totalUnreadConversations', $count);
        });
    }
}

