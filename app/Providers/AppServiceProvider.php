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
        $this->app->bind(
            \App\Contracts\OrderNotificationInterface::class,
            \App\Services\Notification\OrderNotificationManager::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Support\Facades\View::composer('*', function ($view) {
            try {
                $user = auth()->user();
                $cartCount = app(\App\Services\CartService::class)->getCartCount($user);
                $wishlistCount = $user ? app(\App\Services\WishlistService::class)->getWishlistCount($user) : 0;
                $view->with([
                    'headerCartCount' => $cartCount,
                    'headerWishlistCount' => $wishlistCount,
                ]);
            } catch (\Throwable $e) {
                $view->with([
                    'headerCartCount' => 0,
                    'headerWishlistCount' => 0,
                ]);
            }
        });
    }
}
