<?php

namespace App\Providers;

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Anti N+1: relasi yang diakses dalam loop koleksi otomatis di-eager-load sekali.
        Model::automaticallyEagerLoadRelationships();

        // Cegah migrate:fresh / db:wipe tak sengaja di production.
        DB::prohibitDestructiveCommands($this->app->isProduction());

        Password::defaults(fn () => Password::min(8));

        // RBAC web: route ->middleware('can:superadmin').
        Gate::define('superadmin', fn (User $user) => $user->isSuperAdmin());

        // @asset('js/x.js') → URL aset + ?v=filemtime (cache browser panjang, otomatis pecah saat file berubah).
        Blade::directive('asset', fn (string $path) => "<?php echo \\App\\Support\\Asset::url({$path}); ?>");

        // Badge percakapan belum dibaca di sidebar admin.
        View::composer('layouts.partials.sidebar', function ($view) {
            $user = auth()->user();
            $view->with('totalUnreadConversations', $user
                ? Conversation::where('tenant_id', $user->tenant_id)->unreadFor($user)->count()
                : 0);
        });
    }
}
