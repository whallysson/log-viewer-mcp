<?php

use Opcodes\LogViewer\Facades\LogViewer;
use Whallysson\LogViewerMcp\Servers\LogViewerServer;
use Whallysson\LogViewerMcp\Tools\GetErrorSummaryTool;

it('returns overview of all log files', function () {
    LogViewerServer::tool(GetErrorSummaryTool::class)
        ->assertOk()
        ->assertSee('Log Files Overview')
        ->assertSee('laravel.log');
});

it('returns detailed summary for specific file', function () {
    $file = LogViewer::getFiles()->first();

    LogViewerServer::tool(GetErrorSummaryTool::class, ['file' => $file->identifier])
        ->assertOk()
        ->assertSee('Log Summary')
        ->assertSee('Counts by Level')
        ->assertSee('Total');
});

it('returns not found for invalid file', function () {
    LogViewerServer::tool(GetErrorSummaryTool::class, ['file' => 'nonexistent.log'])
        ->assertOk()
        ->assertSee('File not found');
});

it('includes file identifiers in overview table', function () {
    LogViewerServer::tool(GetErrorSummaryTool::class)
        ->assertOk()
        ->assertSee('Identifier');
});
