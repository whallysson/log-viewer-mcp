<?php

use Whallysson\LogViewerMcp\Servers\LogViewerServer;
use Whallysson\LogViewerMcp\Tools\ListLogFilesTool;

it('lists available log files with metadata', function () {
    LogViewerServer::tool(ListLogFilesTool::class)
        ->assertOk()
        ->assertSee('log file(s)')
        ->assertSee('laravel.log')
        ->assertSee('Identifier:');
});

it('returns message when no log files exist', function () {
    config()->set('log-viewer.include_files', [
        __DIR__.'/../fixtures/logs/nonexistent/*.log',
    ]);

    LogViewerServer::tool(ListLogFilesTool::class)
        ->assertOk()
        ->assertSee('No log files found');
});
