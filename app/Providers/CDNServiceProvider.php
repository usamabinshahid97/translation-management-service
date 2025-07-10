<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\CDNService;

class CDNServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(CDNService::class, function ($app) {
            return new CDNService(
                $app['config']['cdn'],
                $app['cache.store'],
                $app['log']->channel()
            );
        });

        $this->app->alias(CDNService::class, 'cdn');
    }

    public function boot()
    {
        // Register CDN helper functions
        if (!function_exists('cdn_asset')) {
            function cdn_asset(string $path, array $options = []): string
            {
                return app(CDNService::class)->asset($path, $options);
            }
        }

        if (!function_exists('cdn_url')) {
            function cdn_url(string $path, array $options = []): string
            {
                return app(CDNService::class)->url($path, $options);
            }
        }
    }
}