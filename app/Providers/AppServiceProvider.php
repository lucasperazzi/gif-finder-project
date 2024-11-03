<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;

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
        Passport::tokensExpireIn(now()->addMinutes(30));
        Passport::personalAccessTokensExpireIn(now()->addMinutes(30));
        Passport::refreshTokensExpireIn(now()->addMinutes(30));
    }
}
