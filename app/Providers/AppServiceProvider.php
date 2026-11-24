<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // General API limit: 60 requests per minute per user (or per IP when guest).
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // Stricter limit for register/login to slow down credential stuffing.
        RateLimiter::for('auth', function (Request $request) {
            return Limit::perMinute(10)->by(strtolower((string) $request->input('email')).'|'.$request->ip());
        });
    }
}
