<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

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
        // Force HTTPS only in production or when not running on localhost
        $request = request();
        $isLocalHost = $request && in_array($request->getHost(), ['127.0.0.1', 'localhost', '::1'], true);
        if (!$isLocalHost && (config('app.env') === 'production' || str_starts_with(config('app.url'), 'https://'))) {
            URL::forceScheme('https');
        }

        \App\Models\Recipe::observe(\App\Observers\RecipeObserver::class);

        // Register AuditObserver for all relevant models
        \App\Models\User::observe(\App\Observers\AuditObserver::class);
        \App\Models\Recipe::observe(\App\Observers\AuditObserver::class);
        \App\Models\Ingredient::observe(\App\Observers\AuditObserver::class);
        \App\Models\ProductionDay::observe(\App\Observers\AuditObserver::class);
        \App\Models\Task::observe(\App\Observers\AuditObserver::class);
        \App\Models\DriveFile::observe(\App\Observers\AuditObserver::class);
    }
}
