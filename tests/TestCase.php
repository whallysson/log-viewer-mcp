<?php

namespace Whallysson\LogViewerMcp\Tests;

use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            \Opcodes\LogViewer\LogViewerServiceProvider::class,
            \Laravel\Mcp\McpServiceProvider::class,
            \Whallysson\LogViewerMcp\LogViewerMcpServiceProvider::class,
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
