<?php

use Opcodes\LogViewer\Facades\LogViewer;
use Whallysson\LogViewerMcp\Servers\LogViewerServer;
use Whallysson\LogViewerMcp\Tools\GetLogEntryTool;

it('requires file parameter', function () {
    LogViewerServer::tool(GetLogEntryTool::class, ['index' => 0])
        ->assertOk()
        ->assertSee("Parameter 'file' is required");
});

it('returns not found for invalid file', function () {
    LogViewerServer::tool(GetLogEntryTool::class, ['file' => 'nonexistent.log', 'index' => 0])
        ->assertOk()
        ->assertSee('File not found');
});

it('returns full log entry details for valid entry', function () {
    $file = LogViewer::getFiles()->first();

    // Search for a known entry to get its index
    $reader = $file->logs();
    $reader->search('SQLSTATE');
    $reader->scan();
    $paginator = $reader->paginate(1);
    $entry = collect($paginator->items())->first();

    expect($entry)->not->toBeNull();

    LogViewerServer::tool(GetLogEntryTool::class, ['file' => $file->identifier, 'index' => $entry->index])
        ->assertOk()
        ->assertSee('Log Entry')
        ->assertSee('SQLSTATE');
});

it('returns not found for invalid index', function () {
    $file = LogViewer::getFiles()->first();

    LogViewerServer::tool(GetLogEntryTool::class, ['file' => $file->identifier, 'index' => 99999])
        ->assertOk()
        ->assertSee('not found');
});
