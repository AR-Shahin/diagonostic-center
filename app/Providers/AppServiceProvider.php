<?php

namespace App\Providers;

use App\Models\Admin;
use App\Models\WebsiteInfo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View as FacadesView;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Super Admin passes every $user->can() / @can check (see config/diagnostic.php → super_admin).
        Gate::before(fn ($user) => $user instanceof Admin && $user->isSuperAdmin() ? true : null);

        FacadesView::composer('admin/*', function (View $view) {
            if (auth('admin')->user()) {
                $view->with('permissions', once(fn () => auth('admin')->user()->permissionNames()));
            }
        });
        FacadesView::composer('*', function (View $view) {
            $view->with('website', once(fn () => WebsiteInfo::first()));
        });

        DB::prohibitDestructiveCommands(
            $this->app->isProduction()
        );

    }
}
