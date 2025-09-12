<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;

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
        Vite::prefetch(concurrency: 3);
        Inertia::share('auth.user', function () {
            $user = auth()->user();
            if (!$user) return null;
            $notifications = $user->unreadNotifications()->take(10)->get();
            return $user->only(['id', 'name', 'email', 'role']) + [
                'notifications' => $notifications->map(function($n) {
                    return [
                        'id' => $n->id,
                        'data' => $n->data,
                        'created_at' => $n->created_at->diffForHumans(),
                    ];
                })
            ];
        });
    }
}
