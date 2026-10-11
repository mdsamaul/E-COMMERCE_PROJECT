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
        \Illuminate\Support\Facades\View::composer('*', function ($view) {
            try {
                $cartCount = app(\App\Services\CartService::class)->getCartCount(auth()->user());
                $view->with('headerCartCount', $cartCount);
            } catch (\Throwable $e) {
                $view->with('headerCartCount', 0);
            }
        });
    }
}
