<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Gate to control visibility of Admin pages/menus
        Gate::define('view-admin-section', function (? \App\Models\User $user) {
            return $user && $user->role === 'admin';
        });
    }
}
