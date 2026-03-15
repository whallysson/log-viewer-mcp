<?php

namespace Whallysson\LogViewerMcp;

use Illuminate\Support\ServiceProvider;

class LogViewerMcpServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/log-viewer-mcp.php', 'log-viewer-mcp');
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/log-viewer-mcp.php' => config_path('log-viewer-mcp.php'),
        ], 'log-viewer-mcp-config');

        if (! config('log-viewer-mcp.enabled')) {
            return;
        }

        $this->loadRoutesFrom(__DIR__.'/../routes/ai.php');
    }
}
