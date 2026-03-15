<?php

use Whallysson\LogViewerMcp\Servers\LogViewerServer;
use Whallysson\LogViewerMcp\Tools\GetErrorSummaryTool;

it('returns overview of all log files', function () {
    $this->mcp(LogViewerServer::class)
        ->tool(GetErrorSummaryTool::class, [])
        ->assertOk()
        ->assertSee('Log Files Overview')
        ->assertSee('laravel.log');
});

it('returns detailed summary for specific file', function () {
    $files = \Opcodes\LogViewer\Facades\LogViewer::getFiles();
    $file = $files->first();

    $this->mcp(LogViewerServer::class)
        ->tool(GetErrorSummaryTool::class, ['file' => $file->identifier])
        ->assertOk()
        ->assertSee('Log Summary')
        ->assertSee('Counts by Level')
        ->assertSee('Total');
});

it('returns not found for invalid file', function () {
    $this->mcp(LogViewerServer::class)
        ->tool(GetErrorSummaryTool::class, ['file' => 'nonexistent.log'])
        ->assertOk()
        ->assertSee('File not found');
});

it('includes file identifiers in overview table', function () {
    $this->mcp(LogViewerServer::class)
        ->tool(GetErrorSummaryTool::class, [])
        ->assertOk()
        ->assertSee('Identifier');
});
