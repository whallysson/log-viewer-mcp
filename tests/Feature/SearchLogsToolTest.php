<?php

use Whallysson\LogViewerMcp\Servers\LogViewerServer;
use Whallysson\LogViewerMcp\Tools\SearchLogsTool;

it('requires at least file or query parameter', function () {
    LogViewerServer::tool(SearchLogsTool::class)
        ->assertOk()
        ->assertSee("Provide at least 'file' or 'query' parameter");
});

it('searches logs by text query', function () {
    LogViewerServer::tool(SearchLogsTool::class, ['query' => 'SQLSTATE'])
        ->assertOk()
        ->assertSee('SQLSTATE')
        ->assertSee('Found');
});

it('returns not found for invalid file identifier', function () {
    LogViewerServer::tool(SearchLogsTool::class, ['file' => 'nonexistent.log'])
        ->assertOk()
        ->assertSee('File not found');
});

it('returns no results message when nothing matches', function () {
    LogViewerServer::tool(SearchLogsTool::class, ['query' => 'nonexistent_xyz_string_99'])
        ->assertOk()
        ->assertSee('No logs found matching your criteria');
});

it('has correct tool name and description', function () {
    LogViewerServer::tool(SearchLogsTool::class, ['query' => 'SQLSTATE'])
        ->assertName('search_logs')
        ->assertDescription('Search logs by text query and/or severity level. Returns matching entries with index, timestamp, level, and message. Use get_log_entry to get the full stack trace of a specific result.');
});
