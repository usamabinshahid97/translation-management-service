<?php

namespace App\Services;

use Illuminate\Cache\Repository as CacheRepository;
use Illuminate\Log\Logger;
use Illuminate\Support\Facades\File;

class CDNService
{
    protected array $config;
    protected CacheRepository $cache;
    protected Logger $logger;

    public function __construct(array $config, CacheRepository $cache, Logger $logger)
    {
        $this->config = $config;
        $this->cache = $cache;
        $this->logger = $logger;
    }

    public function asset(string $path, array $options = []): string
    {
        if (!$this->isEnabled()) {
            return $this->getFallbackUrl($path);
        }

        $provider = $options['provider'] ?? $this->config['default'];
        $cacheKey = $this->getCacheKey($path, $provider);

        if ($this->config['cache']['enabled']) {
            $cachedUrl = $this->cache->get($cacheKey);
            if ($cachedUrl) {
                return $cachedUrl;
            }
        }

        $url = $this->generateCDNUrl($path, $provider, $options);

        if ($this->config['cache']['enabled']) {
            $this->cache->put($cacheKey, $url, $this->config['cache']['ttl']);
        }

        return $url;
    }

    public function url(string $path, array $options = []): string
    {
        return $this->asset($path, $options);
    }

    public function purge(string $path): bool
    {
        try {
            // Clear cache
            if ($this->config['cache']['enabled']) {
                $pattern = $this->config['cache']['key_prefix'] . '*' . md5($path) . '*';
                $this->cache->forget($pattern);
            }

            // In a real implementation, you would call the CDN provider's purge API
            $this->logger->info("CDN cache purged for path: {$path}");
            
            return true;
        } catch (\Exception $e) {
            $this->logger->error("CDN purge failed for path: {$path}", ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function preload(array $paths): array
    {
        $results = [];
        
        foreach ($paths as $path) {
            try {
                $url = $this->asset($path);
                $results[$path] = ['success' => true, 'url' => $url];
            } catch (\Exception $e) {
                $results[$path] = ['success' => false, 'error' => $e->getMessage()];
            }
        }

        return $results;
    }

    public function getAssetVersion(string $path): string
    {
        if (!$this->config['asset_versioning']['enabled']) {
            return '';
        }

        $fullPath = public_path($path);
        
        if (File::exists($fullPath)) {
            return $this->config['asset_versioning']['auto_version'] 
                ? (string) File::lastModified($fullPath) 
                : config('app.version', '1.0.0');
        }

        return '';
    }

    public function isHealthy(): bool
    {
        if (!$this->isEnabled()) {
            return true; // Fallback is always healthy
        }

        // In a real implementation, you would check CDN health
        return true;
    }

    protected function isEnabled(): bool
    {
        return $this->config['enabled'] ?? false;
    }

    protected function generateCDNUrl(string $path, string $provider, array $options = []): string
    {
        $providerConfig = $this->config['providers'][$provider] ?? null;
        
        if (!$providerConfig) {
            $this->logger->warning("CDN provider '{$provider}' not configured, falling back to local");
            return $this->getFallbackUrl($path);
        }

        $baseUrl = rtrim($providerConfig['base_url'], '/');
        $assetPath = $this->getAssetPath($path, $providerConfig);
        $version = $this->getAssetVersion($path);

        $url = $baseUrl . '/' . ltrim($assetPath, '/');

        if ($version && $this->config['asset_versioning']['enabled']) {
            $versionParam = $this->config['asset_versioning']['version_parameter'];
            $separator = strpos($url, '?') !== false ? '&' : '?';
            $url .= $separator . $versionParam . '=' . $version;
        }

        return $url;
    }

    protected function getAssetPath(string $path, array $providerConfig): string
    {
        $path = ltrim($path, '/');
        $extension = pathinfo($path, PATHINFO_EXTENSION);
        
        // Map file extensions to CDN paths
        $extensionMap = [
            'css' => 'css',
            'js' => 'js',
            'png' => 'images',
            'jpg' => 'images',
            'jpeg' => 'images',
            'gif' => 'images',
            'svg' => 'images',
            'webp' => 'images',
            'woff' => 'fonts',
            'woff2' => 'fonts',
            'ttf' => 'fonts',
            'eot' => 'fonts',
        ];

        $pathType = $extensionMap[$extension] ?? 'css';
        $basePath = $providerConfig['paths'][$pathType] ?? '';

        return $basePath . $path;
    }

    protected function getFallbackUrl(string $path): string
    {
        if ($this->config['fallback']['enabled']) {
            $fallbackPath = $this->config['fallback']['local_path'];
            return url($fallbackPath . ltrim($path, '/'));
        }

        return url($path);
    }

    protected function getCacheKey(string $path, string $provider): string
    {
        return $this->config['cache']['key_prefix'] . md5($provider . ':' . $path);
    }
}