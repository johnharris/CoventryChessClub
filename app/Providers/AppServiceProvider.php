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
        // When APP_URL is an https address, generate https links and asset URLs.
        // Without this, a site sitting behind a reverse proxy or load balancer
        // that terminates TLS emits http:// asset URLs, which browsers then
        // block as mixed content — the stylesheet silently fails to load.
        if (str_starts_with((string) config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }
    }
}
