<?php

namespace App\Providers;

use App\Models\WebsiteInfo;
use Illuminate\Support\Facades\DB;
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
        FacadesView::composer('admin/*', function (View $view) {
            if (auth('admin')->user()) {
                $view->with('permissions', auth('admin')->user()->getAllPermissions()->pluck('name')->toArray());
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
