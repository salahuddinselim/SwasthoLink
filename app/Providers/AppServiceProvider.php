<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
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
        View::composer('layouts.notifications-bell', function ($view) {
            $user = auth()->user();

            $view->with([
                'notifications' => $user ? $user->notifications()->latest()->limit(8)->get() : collect(),
                'unreadCount' => $user ? $user->notifications()->whereNull('read_at')->count() : 0,
            ]);
        });
    }
}
