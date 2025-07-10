<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\CDNService;

class CDNMiddleware
{
    protected CDNService $cdnService;

    public function __construct(CDNService $cdnService)
    {
        $this->cdnService = $cdnService;
    }

    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Add CDN headers for asset responses
        if ($this->isAssetRequest($request)) {
            $this->addCDNHeaders($response);
        }

        return $response;
    }

    protected function isAssetRequest(Request $request): bool
    {
        $path = $request->getPathInfo();
        $assetExtensions = ['css', 'js', 'png', 'jpg', 'jpeg', 'gif', 'svg', 'webp', 'woff', 'woff2', 'ttf', 'eot'];
        
        foreach ($assetExtensions as $extension) {
            if (str_ends_with($path, '.' . $extension)) {
                return true;
            }
        }

        return false;
    }

    protected function addCDNHeaders($response): void
    {
        $config = config('cdn');
        
        if (!$config['enabled']) {
            return;
        }

        $provider = $config['default'];
        $headers = $config['providers'][$provider]['headers'] ?? [];

        foreach ($headers as $key => $value) {
            $response->header($key, $value);
        }

        // Add security headers
        if (isset($config['security']['cors_headers'])) {
            foreach ($config['security']['cors_headers'] as $key => $value) {
                $response->header($key, $value);
            }
        }

        // Add compression headers
        if ($config['compression']['enabled']) {
            $response->header('Content-Encoding', 'gzip');
            $response->header('Vary', 'Accept-Encoding');
        }
    }
}