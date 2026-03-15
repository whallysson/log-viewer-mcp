# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project

Laravel package that exposes `opcodesio/log-viewer` as an MCP (Model Context Protocol) server. Allows AI agents to list, search, and inspect application logs via MCP tools.

- **Package:** `whallysson/log-viewer-mcp`
- **PHP:** ^8.1
- **Dependencies:** `laravel/mcp` ^0.1, `opcodesio/log-viewer` ^3.0|^4.0
- **Test framework:** Pest 3 (never PHPUnit)

## Commands

```bash
# Install dependencies
composer install

# Run tests
./vendor/bin/pest

# Run single test file
./vendor/bin/pest tests/Feature/SomeTest.php

# Run filtered test
./vendor/bin/pest --filter="test name"

# Publish config to host app
php artisan vendor:publish --tag=log-viewer-mcp-config
```

## Architecture

```
src/
├── LogViewerMcpServiceProvider.php   # Service provider: merges config, registers routes
├── Servers/
│   └── LogViewerServer.php           # MCP server definition (name, version, tools list)
└── Tools/                            # MCP tools (each extends Laravel\Mcp\Server\Tool)
    ├── ListLogFilesTool.php           # list_log_files: discover available log files
    ├── SearchLogsTool.php             # search_logs: search by query/level with pagination
    ├── GetLogEntryTool.php            # get_log_entry: full entry detail with stack trace
    └── GetErrorSummaryTool.php        # get_error_summary: severity counts + recent errors
config/
└── log-viewer-mcp.php                # Config: enabled, max_results, max_log_text_length
routes/
└── ai.php                            # Registers MCP local server via Mcp::local()
```

### Key patterns

- **Tool structure:** Each tool extends `Laravel\Mcp\Server\Tool`, defines `$name`, `$description`, `schema()` (JSON Schema params), and `handle()` (returns `Response::text()`).
- **Server registration:** `LogViewerServer` extends `Laravel\Mcp\Server`, declares tool classes in `$tools` array. Route file calls `Mcp::local('log-viewer', LogViewerServer::class)`.
- **Log access:** All tools use `Opcodes\LogViewer\Facades\LogViewer` facade — `getFiles()`, `getFile($id)`, `getFolder($id)`. Log reading uses `$file->logs()` which returns a chainable reader (`.search()`, `.only()`, `.reverse()`, `.scan()`, `.paginate()`).
- **Config guards:** `max_results` caps search pagination; `max_log_text_length` truncates stack traces in `GetLogEntryTool`.
- **Feature toggle:** Package disables entirely when `LOG_VIEWER_MCP_ENABLED=false`.
