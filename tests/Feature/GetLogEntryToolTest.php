<?php

use Whallysson\LogViewerMcp\Servers\LogViewerServer;
use Whallysson\LogViewerMcp\Tools\GetLogEntryTool;

it('requires file parameter', function () {
    $this->mcp(LogViewerServer::class)
        ->tool(GetLogEntryTool::class, ['index' => 0])
        ->assertOk()
        ->assertSee("Parameter 'file' is required");
});

it('returns not found for invalid file', function () {
    $this->mcp(LogViewerServer::class)
        ->tool(GetLogEntryTool::class, ['file' => 'nonexistent.log', 'index' => 0])
        ->assertOk()
        ->assertSee('File not found');
});

it('returns full log entry details', function () {
    $files = \Opcodes\LogViewer\Facades\LogViewer::getFiles();
    $file = $files->first();

    $this->mcp(LogViewerServer::class)
        ->tool(GetLogEntryTool::class, ['file' => $file->identifier, 'index' => 0])
        ->assertOk()
        ->assertSee('Log Entry #0')
        ->assertSee('Level')
        ->assertSee('Timestamp');
});

it('returns not found for invalid index', function () {
    $files = \Opcodes\LogViewer\Facades\LogViewer::getFiles();
    $file = $files->first();

    $this->mcp(LogViewerServer::class)
        ->tool(GetLogEntryTool::class, ['file' => $file->identifier, 'index' => 99999])
        ->assertOk()
        ->assertSee('not found');
});
