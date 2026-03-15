<?php

namespace Whallysson\LogViewerMcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;
use Opcodes\LogViewer\Facades\LogViewer;

class GetLogEntryTool extends Tool
{
    protected string $name = 'get_log_entry';

    protected string $description = 'Get full details of a specific log entry including complete stack trace, context, and extra data. Use the index and file identifier from search_logs results.';

    public function schema(JsonSchema $schema): array
    {
        return [
            'file' => $schema->string()
                ->description('File identifier (required). Get from search_logs or list_log_files.')
                ->required(),
            'index' => $schema->integer()
                ->description('Log entry index within the file (required). Get from search_logs results.')
                ->required(),
        ];
    }

    public function handle(Request $request): Response
    {
        $fileIdentifier = $request->get('file');
        $index = (int) $request->get('index');

        if (! $fileIdentifier) {
            return Response::text("Parameter 'file' is required. Use list_log_files to discover available files.");
        }

        $file = LogViewer::getFile($fileIdentifier);
        if (! $file) {
            return Response::text("File not found: {$fileIdentifier}");
        }

        try {
            $logReader = $file->logs();
            $logReader->search("log-index:{$index}");
            $logReader->scan();

            $paginator = $logReader->paginate(1);
            $log = $paginator->getCollection()->first();
        } catch (\Throwable $e) {
            return Response::text("Error reading log: {$e->getMessage()}");
        }

        if (! $log) {
            return Response::text("Log entry with index {$index} not found in file {$fileIdentifier}.");
        }

        $datetime = $log->datetime?->toDateTimeString() ?? 'unknown';
        $level = strtoupper($log->level ?? 'UNKNOWN');
        $message = $log->message ?? '';
        $fullText = $log->getOriginalText() ?? '';

        $maxLen = (int) config('log-viewer-mcp.max_log_text_length', 10000);
        if (strlen($fullText) > $maxLen) {
            $fullText = substr($fullText, 0, $maxLen)."\n\n... [truncated at {$maxLen} characters]";
        }

        $text = "## Log Entry #{$index}\n\n";
        $text .= "**Level**: {$level}\n";
        $text .= "**Timestamp**: {$datetime}\n";
        $text .= "**File**: {$file->name} (`{$fileIdentifier}`)\n\n";
        $text .= "### Message\n\n{$message}\n\n";

        if (! empty($fullText)) {
            $text .= "### Full Log Text\n\n```\n{$fullText}\n```\n\n";
        }

        if (! empty($log->context) && (is_array($log->context) ? count($log->context) > 0 : true)) {
            $contextJson = json_encode($log->context, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            $text .= "### Context\n\n```json\n{$contextJson}\n```\n\n";
        }

        if (! empty($log->extra) && (is_array($log->extra) ? count($log->extra) > 0 : true)) {
            $extraJson = json_encode($log->extra, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            $text .= "### Extra Data\n\n```json\n{$extraJson}\n```\n\n";
        }

        return Response::text($text);
    }
}
