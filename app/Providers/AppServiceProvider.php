<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

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
        // Global eager loading for user roles to optimize permission checks in the sidebar
        if (request()->is('login') || request()->is('logout')) return;
        
        \Illuminate\Support\Facades\View::composer('*', function ($view) {
            if (\Illuminate\Support\Facades\Auth::check()) {
                // Ensure the role relation is loaded only once per request
                $user = \Illuminate\Support\Facades\Auth::user();
                if (!$user->relationLoaded('role')) {
                    $user->load('role');
                }
            }
        });
    }
}
