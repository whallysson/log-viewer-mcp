<?php

use Whallysson\LogViewerMcp\Servers\LogViewerServer;
use Whallysson\LogViewerMcp\Tools\SearchLogsTool;

it('requires at least file or query parameter', function () {
    $this->mcp(LogViewerServer::class)
        ->tool(SearchLogsTool::class, [])
        ->assertOk()
        ->assertSee("Provide at least 'file' or 'query' parameter");
});

it('searches logs by text query', function () {
    $this->mcp(LogViewerServer::class)
        ->tool(SearchLogsTool::class, ['query' => 'SQLSTATE'])
        ->assertOk()
        ->assertSee('SQLSTATE')
        ->assertSee('Found');
});

it('filters logs by severity level', function () {
    $this->mcp(LogViewerServer::class)
        ->tool(SearchLogsTool::class, ['query' => '.', 'level' => 'error'])
        ->assertOk()
        ->assertSee('ERROR')
        ->assertDontSee('[INFO]')
        ->assertDontSee('[DEBUG]');
});

it('returns not found for invalid file identifier', function () {
    $this->mcp(LogViewerServer::class)
        ->tool(SearchLogsTool::class, ['file' => 'nonexistent.log'])
        ->assertOk()
        ->assertSee('File not found');
});

it('paginates search results', function () {
    $this->mcp(LogViewerServer::class)
        ->tool(SearchLogsTool::class, ['query' => '.', 'per_page' => 2, 'page' => 1])
        ->assertOk()
        ->assertSee('page 1');
});
