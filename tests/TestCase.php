<?php

namespace Whallysson\LogViewerMcp\Tests;

use Laravel\Mcp\Server\McpServiceProvider;
use Opcodes\LogViewer\LogViewerServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Whallysson\LogViewerMcp\LogViewerMcpServiceProvider;

abstract class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            LogViewerServiceProvider::class,
            McpServiceProvider::class,
            LogViewerMcpServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('log-viewer-mcp.enabled', true);
        $app['config']->set('log-viewer.include_files', [
            __DIR__.'/fixtures/logs/*.log',
        ]);
    }
}
