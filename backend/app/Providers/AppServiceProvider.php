<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

use App\Models\Conference;

use App\Policies\ConferencePolicy;

use App\Services\AuthService;
use App\Services\ConferenceLockService;



class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(AuthService::class, function ($app) {
            return new AuthService();
        });

        $this->app->singleton(ConferenceLockService::class, function ($app) {
            return new ConferenceLockService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Conference::class, ConferencePolicy::class);
    }
}
