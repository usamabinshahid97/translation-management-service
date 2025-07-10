<?php

return [
    
    'enabled' => env('CDN_ENABLED', false),
    
    'default' => env('CDN_DEFAULT_PROVIDER', 'cloudflare'),
    
    'providers' => [
        'cloudflare' => [
            'base_url' => env('CDN_CLOUDFLARE_BASE_URL', 'https://cdn.example.com'),
            'headers' => [
                'Cache-Control' => 'public, max-age=31536000, immutable',
                'Vary' => 'Accept-Encoding',
            ],
            'paths' => [
                'css' => 'assets/css/',
                'js' => 'assets/js/',
                'images' => 'assets/images/',
                'fonts' => 'assets/fonts/',
            ],
        ],
        
        'aws_cloudfront' => [
            'base_url' => env('CDN_AWS_CLOUDFRONT_BASE_URL', 'https://d1234567890.cloudfront.net'),
            'headers' => [
                'Cache-Control' => 'public, max-age=31536000, immutable',
                'Vary' => 'Accept-Encoding',
            ],
            'paths' => [
                'css' => 'assets/css/',
                'js' => 'assets/js/',
                'images' => 'assets/images/',
                'fonts' => 'assets/fonts/',
            ],
        ],
        
        'azure' => [
            'base_url' => env('CDN_AZURE_BASE_URL', 'https://azurecdn.net'),
            'headers' => [
                'Cache-Control' => 'public, max-age=31536000, immutable',
                'Vary' => 'Accept-Encoding',
            ],
            'paths' => [
                'css' => 'assets/css/',
                'js' => 'assets/js/',
                'images' => 'assets/images/',
                'fonts' => 'assets/fonts/',
            ],
        ],
    ],
    
    'fallback' => [
        'enabled' => true,
        'local_path' => '/assets/',
    ],
    
    'cache' => [
        'enabled' => true,
        'ttl' => 3600, // 1 hour
        'key_prefix' => 'cdn_',
    ],
    
    'asset_versioning' => [
        'enabled' => env('CDN_ASSET_VERSIONING', true),
        'version_parameter' => 'v',
        'auto_version' => true,
    ],
    
    'compression' => [
        'enabled' => true,
        'formats' => ['gzip', 'brotli'],
        'min_size' => 1024, // bytes
    ],
    
    'security' => [
        'allowed_origins' => [
            'https://example.com',
            'https://www.example.com',
        ],
        'cors_headers' => [
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => 'GET, HEAD, OPTIONS',
            'Access-Control-Allow-Headers' => 'Origin, Content-Type, Accept, Authorization',
        ],
    ],
    
];