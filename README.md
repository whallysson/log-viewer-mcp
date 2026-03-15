# Log Viewer MCP

[![Tests](https://github.com/whallysson/log-viewer-mcp/actions/workflows/tests.yml/badge.svg)](https://github.com/whallysson/log-viewer-mcp/actions)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/whallysson/log-viewer-mcp.svg)](https://packagist.org/packages/whallysson/log-viewer-mcp)
[![PHP Version](https://img.shields.io/packagist/php-v/whallysson/log-viewer-mcp.svg)](https://packagist.org/packages/whallysson/log-viewer-mcp)
[![License](https://img.shields.io/packagist/l/whallysson/log-viewer-mcp.svg)](https://github.com/whallysson/log-viewer-mcp/blob/main/LICENSE)

An [MCP (Model Context Protocol)](https://modelcontextprotocol.io/) server that exposes [opcodesio/log-viewer](https://github.com/opcodesio/log-viewer) to AI agents. Let Claude, GPT, and other AI assistants read, search, and analyze your Laravel application logs.

Built on top of the official [laravel/mcp](https://github.com/laravel/mcp) package using STDIO transport.

## Why?

When debugging production issues with AI, the agent needs to see your logs. Instead of copy-pasting stack traces, this package gives your AI assistant direct access to your log files — with search, filtering, and structured output.

**4 tools, zero config.** Install the package and your AI agent can immediately:

- List all available log files
- Search logs by text or severity level
- Get full details of any log entry (stack trace, context, extra data)
- Get a health overview with error counts and recent failures

## Requirements

- PHP 8.2+
- Laravel 10.x, 11.x, or 12.x
- [opcodesio/log-viewer](https://github.com/opcodesio/log-viewer) 3.x or 4.x
- [laravel/mcp](https://github.com/laravel/mcp) 0.6+

## Installation

```bash
composer require whallysson/log-viewer-mcp
```

The package auto-discovers its service provider. No additional setup required.

### Publish Configuration (Optional)

```bash
php artisan vendor:publish --tag=log-viewer-mcp-config
```

## Configuration

```php
// config/log-viewer-mcp.php

return [
    // Enable or disable the MCP server
    'enabled' => env('LOG_VIEWER_MCP_ENABLED', true),

    // Maximum results per search page
    'max_results' => 50,

    // Truncate full log text (stack traces) longer than this
    'max_log_text_length' => 10000,
];
```

## Available Tools

### `list_log_files`

Discover available log files with metadata (name, size, date range, type).

| Parameter | Type   | Required | Description                     |
|-----------|--------|----------|---------------------------------|
| `folder`  | string | No       | Folder identifier to filter by  |

### `search_logs`

Search logs by text query and/or severity level with pagination.

| Parameter  | Type    | Required | Description                                              |
|------------|---------|----------|----------------------------------------------------------|
| `file`     | string  | No*      | File identifier from `list_log_files`                    |
| `query`    | string  | No*      | Text to search (case-insensitive regex)                  |
| `level`    | string  | No       | Severity: emergency, alert, critical, error, warning, notice, info, debug |
| `per_page` | integer | No       | Results per page (default: 15, max: 50)                  |
| `page`     | integer | No       | Page number (default: 1)                                 |

*At least `file` or `query` is required.

### `get_log_entry`

Get full details of a specific log entry including stack trace, context, and extra data.

| Parameter | Type    | Required | Description                         |
|-----------|---------|----------|-------------------------------------|
| `file`    | string  | Yes      | File identifier                     |
| `index`   | integer | Yes      | Log entry index from search results |

### `get_error_summary`

Get a high-level health overview: counts per severity level and most recent errors.

| Parameter             | Type    | Required | Description                                      |
|-----------------------|---------|----------|--------------------------------------------------|
| `file`                | string  | No       | File identifier (omit for overview of all files)  |
| `recent_errors_count` | integer | No       | Number of recent errors to include (default: 5, max: 20) |

## Usage with Claude Desktop

Add to your `claude_desktop_config.json`:

```json
{
  "mcpServers": {
    "log-viewer": {
      "command": "php",
      "args": ["artisan", "mcp:start", "log-viewer"],
      "cwd": "/path/to/your/laravel/app"
    }
  }
}
```

Then ask Claude things like:

- *"Check my application logs for recent errors"*
- *"Search for any SQLSTATE errors in the last log file"*
- *"Give me a health summary of the application"*
- *"Show me the full stack trace for that error"*

## Suggested Workflow

The tools are designed to be used in sequence:

```
1. list_log_files      → discover what log files exist
2. get_error_summary   → quick health check
3. search_logs         → find specific errors
4. get_log_entry       → get full details + stack trace
```

## Testing

```bash
composer test
```

## Code Style

This package uses [Laravel Pint](https://laravel.com/docs/pint) for code style.

```bash
# Check for style issues
composer lint

# Fix style issues
composer lint:fix
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Contributions are welcome! Please see the [pull request template](.github/PULL_REQUEST_TEMPLATE.md) for details.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/new-tool`)
3. Ensure tests pass (`composer test`) and code style is clean (`composer lint`)
4. Submit a pull request

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
