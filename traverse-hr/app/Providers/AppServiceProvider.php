<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
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
        // Staff bank details and signed paperwork must never travel over
        // plain HTTP in production. Shared hosting terminates TLS upstream,
        // so force URL generation to https rather than trusting the scheme
        // the app sees internally.
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
