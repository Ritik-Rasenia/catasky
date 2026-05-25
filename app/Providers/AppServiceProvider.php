<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Pagination\Paginator;

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
        Paginator::useBootstrapFive();

        // Implicitly grant "Super Admin" role all permissions
        // This works in the app via Gate::before for can() check
        \Illuminate\Support\Facades\Gate::before(function ($user, $ability) {
            return $user->hasRole('Super Admin') ? true : null;
        });

        // Blade conditional helper for permissions using Laravel policies
        Blade::if('permission', function ($permission) {
            return auth()->check() && auth()->user()->can($permission);
        });
    }
}
