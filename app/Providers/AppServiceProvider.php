<?php

namespace App\Providers;

use App\Services\Jwt\JwksService;
use App\Services\Jwt\JwtIssuer;
use App\Services\Jwt\JwtKeys;
use App\Services\Jwt\JwtVerifier;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(JwtKeys::class, fn () => JwtKeys::loadFromConfig());
        $this->app->singleton(JwtIssuer::class);
        $this->app->singleton(JwtVerifier::class);
        $this->app->singleton(JwksService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('login', function (Request $request) {
            $email = $request->input('email', 'unknown');

            return Limit::perMinute(5)->by($request->ip().'|'.strtolower($email));
        });
    }
}
