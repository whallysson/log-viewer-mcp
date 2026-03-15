<?php

namespace Whallysson\LogViewerMcp\Servers;

use Laravel\Mcp\Server;
use Whallysson\LogViewerMcp\Tools\GetErrorSummaryTool;
use Whallysson\LogViewerMcp\Tools\GetLogEntryTool;
use Whallysson\LogViewerMcp\Tools\ListLogFilesTool;
use Whallysson\LogViewerMcp\Tools\SearchLogsTool;

class LogViewerServer extends Server
{
    protected string $name = 'log-viewer';

    protected string $version = '1.0.0';

    protected string $instructions = <<<'MARKDOWN'
MCP server for reading and analyzing Laravel application logs via opcodesio/log-viewer.

## Suggested workflow

1. Use `list_log_files` to discover available log files
2. Use `get_error_summary` to get a quick health overview
3. Use `search_logs` to find specific errors by query or level
4. Use `get_log_entry` to get full details (stack trace, context) of a specific error

## Tips

- Always start with `list_log_files` to get file identifiers
- Use `search_logs` with `level: "error"` to quickly find errors
- The `index` and `file` from search results are needed for `get_log_entry`
- Stack traces may be truncated for very large logs
MARKDOWN;

    protected array $tools = [
        ListLogFilesTool::class,
        SearchLogsTool::class,
        GetLogEntryTool::class,
        GetErrorSummaryTool::class,
    ];
}
