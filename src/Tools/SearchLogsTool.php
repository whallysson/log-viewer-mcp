<?php

namespace Whallysson\LogViewerMcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;
use Opcodes\LogViewer\Facades\LogViewer;

class SearchLogsTool extends Tool
{
    protected string $name = 'search_logs';

    protected string $description = 'Search logs by text query and/or severity level. Returns matching entries with index, timestamp, level, and message. Use get_log_entry to get the full stack trace of a specific result.';

    public function schema(JsonSchema $schema): array
    {
        return [
            'file' => $schema->string()
                ->description('File identifier from list_log_files. Omit to search all files.'),
            'query' => $schema->string()
                ->description('Text to search for (case-insensitive regex). Examples: "SQLSTATE", "Connection refused", "Class.*not found".'),
            'level' => $schema->string()
                ->description('Filter by severity: emergency, alert, critical, error, warning, notice, info, debug.'),
            'per_page' => $schema->integer()
                ->description('Results per page (default: 15, max: 50).'),
            'page' => $schema->integer()
                ->description('Page number (default: 1).'),
        ];
    }

    public function handle(Request $request): Response
    {
        $fileIdentifier = $request->get('file');
        $query = $request->get('query');
        $level = $request->get('level');
        $maxResults = (int) config('log-viewer-mcp.max_results', 50);
        $perPage = min((int) ($request->get('per_page') ?? 15), $maxResults);
        $page = max(1, (int) ($request->get('page') ?? 1));

        if (! $fileIdentifier && ! $query) {
            return Response::text("Provide at least 'file' or 'query' parameter. Use list_log_files to discover available files.");
        }

        if ($fileIdentifier) {
            $file = LogViewer::getFile($fileIdentifier);
            if (! $file) {
                return Response::text("File not found: {$fileIdentifier}. Use list_log_files to see available files.");
            }
            $logReader = $file->logs();
        } else {
            $logReader = LogViewer::getFiles()->logs();
        }

        if ($query) {
            $logReader->search($query);
        }
        if ($level) {
            $logReader->only(strtolower($level));
        }

        $logReader->reverse();

        try {
            $logReader->scan();
        } catch (\Throwable $e) {
            return Response::text("Error scanning logs: {$e->getMessage()}");
        }

        $paginator = $logReader->paginate($perPage, $page);

        if ($paginator->total() === 0) {
            return Response::text('No logs found matching your criteria.');
        }

        $text = "Found {$paginator->total()} log(s)";
        $text .= " (page {$page} of {$paginator->lastPage()}, {$perPage} per page):\n\n";

        foreach ($paginator->items() as $log) {
            $datetime = $log->datetime?->toDateTimeString() ?? 'unknown';
            $levelStr = strtoupper($log->level ?? 'UNKNOWN');
            $message = mb_substr($log->message ?? '', 0, 200);
            $fileId = $log->fileIdentifier ?? '';

            $text .= "---\n";
            $text .= "**[{$levelStr}]** {$datetime}\n";
            $text .= "Index: `{$log->index}` | File: `{$fileId}`\n";
            $text .= "Message: {$message}\n\n";
        }

        if ($paginator->hasMorePages()) {
            $nextPage = $page + 1;
            $text .= "\n_More results available. Use page={$nextPage} to see next page._\n";
        }

        return Response::text($text);
    }
}
