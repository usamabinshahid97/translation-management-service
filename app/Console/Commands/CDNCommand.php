<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\CDNService;
use Illuminate\Support\Facades\File;

class CDNCommand extends Command
{
    protected $signature = 'cdn:manage 
                           {action : The action to perform (purge|preload|health|info)}
                           {--path= : Asset path to purge or preload}
                           {--provider= : CDN provider to use}
                           {--all : Apply to all assets}';

    protected $description = 'Manage CDN operations';

    protected CDNService $cdnService;

    public function __construct(CDNService $cdnService)
    {
        parent::__construct();
        $this->cdnService = $cdnService;
    }

    public function handle()
    {
        $action = $this->argument('action');

        switch ($action) {
            case 'purge':
                return $this->handlePurge();
            case 'preload':
                return $this->handlePreload();
            case 'health':
                return $this->handleHealth();
            case 'info':
                return $this->handleInfo();
            default:
                $this->error("Unknown action: {$action}");
                return Command::FAILURE;
        }
    }

    protected function handlePurge(): int
    {
        $path = $this->option('path');
        $all = $this->option('all');

        if (!$path && !$all) {
            $this->error('Please provide --path or --all option');
            return Command::FAILURE;
        }

        if ($all) {
            $this->info('Purging all CDN cache...');
            $paths = $this->getAllAssetPaths();
            
            $progressBar = $this->output->createProgressBar(count($paths));
            $progressBar->start();

            foreach ($paths as $assetPath) {
                $this->cdnService->purge($assetPath);
                $progressBar->advance();
            }

            $progressBar->finish();
            $this->newLine();
            $this->info('All CDN cache purged successfully!');
        } else {
            $this->info("Purging CDN cache for: {$path}");
            
            if ($this->cdnService->purge($path)) {
                $this->info('CDN cache purged successfully!');
            } else {
                $this->error('Failed to purge CDN cache');
                return Command::FAILURE;
            }
        }

        return Command::SUCCESS;
    }

    protected function handlePreload(): int
    {
        $path = $this->option('path');
        $all = $this->option('all');

        if (!$path && !$all) {
            $this->error('Please provide --path or --all option');
            return Command::FAILURE;
        }

        $paths = $all ? $this->getAllAssetPaths() : [$path];

        $this->info('Preloading CDN assets...');
        $results = $this->cdnService->preload($paths);

        $successful = 0;
        $failed = 0;

        foreach ($results as $assetPath => $result) {
            if ($result['success']) {
                $successful++;
                $this->line("<info>✓</info> {$assetPath} → {$result['url']}");
            } else {
                $failed++;
                $this->line("<error>✗</error> {$assetPath} → {$result['error']}");
            }
        }

        $this->newLine();
        $this->info("Preloading completed: {$successful} successful, {$failed} failed");

        return $failed > 0 ? Command::FAILURE : Command::SUCCESS;
    }

    protected function handleHealth(): int
    {
        $this->info('Checking CDN health...');

        if ($this->cdnService->isHealthy()) {
            $this->info('✓ CDN is healthy');
            return Command::SUCCESS;
        } else {
            $this->error('✗ CDN health check failed');
            return Command::FAILURE;
        }
    }

    protected function handleInfo(): int
    {
        $config = config('cdn');

        $this->info('CDN Configuration');
        $this->line('==================');
        $this->line("Enabled: " . ($config['enabled'] ? 'Yes' : 'No'));
        $this->line("Default Provider: " . ($config['default'] ?? 'None'));
        $this->line("Cache Enabled: " . ($config['cache']['enabled'] ? 'Yes' : 'No'));
        $this->line("Cache TTL: " . ($config['cache']['ttl'] ?? 'N/A') . ' seconds');
        $this->line("Asset Versioning: " . ($config['asset_versioning']['enabled'] ? 'Yes' : 'No'));

        $this->newLine();
        $this->info('Available Providers:');
        foreach ($config['providers'] as $name => $providerConfig) {
            $this->line("  • {$name}: {$providerConfig['base_url']}");
        }

        if ($config['enabled']) {
            $this->newLine();
            $this->info('Testing CDN URL generation:');
            $testPaths = ['css/app.css', 'js/app.js', 'images/logo.png'];
            
            foreach ($testPaths as $testPath) {
                $url = $this->cdnService->asset($testPath);
                $this->line("  {$testPath} → {$url}");
            }
        }

        return Command::SUCCESS;
    }

    protected function getAllAssetPaths(): array
    {
        $paths = [];
        $assetDirectories = [
            'css' => public_path('css'),
            'js' => public_path('js'),
            'images' => public_path('images'),
            'fonts' => public_path('fonts'),
        ];

        foreach ($assetDirectories as $type => $directory) {
            if (File::isDirectory($directory)) {
                $files = File::allFiles($directory);
                foreach ($files as $file) {
                    $relativePath = str_replace(public_path(), '', $file->getPathname());
                    $paths[] = ltrim($relativePath, '/');
                }
            }
        }

        return $paths;
    }
}