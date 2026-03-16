<?php

namespace Whallysson\LogViewerMcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;
use Opcodes\LogViewer\Facades\LogViewer;

class GetErrorSummaryTool extends Tool
{
    protected string $name = 'get_error_summary';

    protected string $description = 'Get a high-level summary of log files: counts per severity level, per file, and the most recent critical/error entries. Use this to quickly assess the health of the application.';

    public function schema(JsonSchema $schema): array
    {
        return [
            'file' => $schema->string()
                ->description('File identifier to summarize. Omit for overview of all files.'),
            'recent_errors_count' => $schema->integer()
                ->description('Number of recent error/critical entries to include (default: 5, max: 20).'),
        ];
    }

    public function handle(Request $request): Response
    {
        $fileIdentifier = $request->get('file');
        $recentCount = min((int) ($request->get('recent_errors_count') ?? 5), 20);

        if ($fileIdentifier) {
            return $this->summarizeFile($fileIdentifier, $recentCount);
        }

        return $this->summarizeAll();
    }

    protected function summarizeFile(string $fileIdentifier, int $recentCount): Response
    {
        $file = LogViewer::getFile($fileIdentifier);
        if (! $file) {
            return Response::text("File not found: {$fileIdentifier}");
        }

        $reader = $file->logs();

        try {
            $reader->scan();
        } catch (\Throwable $e) {
            return Response::text("Error scanning file: {$e->getMessage()}");
        }

        $levelCounts = $reader->getLevelCounts();

        $earliest = date('Y-m-d H:i:s', $file->earliestTimestamp());
        $latest = date('Y-m-d H:i:s', $file->latestTimestamp());

        $text = "## Log Summary: {$file->name}\n\n";
        $text .= "**Size**: {$file->sizeFormatted()}\n";
        $text .= "**Period**: {$earliest} to {$latest}\n\n";
        $text .= "### Counts by Level\n\n";

        $total = 0;
        foreach ($levelCounts as $lc) {
            $text .= "- **{$lc->level->getName()}**: {$lc->count}\n";
            $total += $lc->count;
        }
        $text .= "\n**Total**: {$total}\n\n";

        $errorReader = $file->logs();
        $errorReader->only(['EMERGENCY', 'ALERT', 'CRITICAL', 'ERROR']);
        $errorReader->reverse();

        try {
            $errorReader->scan();
            $errors = $errorReader->get($recentCount);
        } catch (\Throwable $e) {
            $errors = [];
        }

        if (! empty($errors)) {
            $text .= "### Recent Errors ({$recentCount} most recent)\n\n";
            foreach ($errors as $log) {
                $datetime = $log->datetime?->toDateTimeString() ?? 'unknown';
                $level = strtoupper($log->level ?? 'ERROR');
                $msg = mb_substr($log->message ?? '', 0, 150);
                $text .= "- **[{$level}]** {$datetime} -- {$msg} (index: `{$log->index}`)\n";
            }
        } else {
            $text .= "### No errors found in this file.\n";
        }

        return Response::text($text);
    }

    protected function summarizeAll(): Response
    {
        $files = LogViewer::getFiles();

        if ($files->isEmpty()) {
            return Response::text('No log files found.');
        }

        $text = "## Log Files Overview\n\n";
        $text .= "| File | Identifier | Size | Type | Period |\n";
        $text .= "|------|------------|------|------|--------|\n";

        foreach ($files as $file) {
            $earliest = date('Y-m-d', $file->earliestTimestamp());
            $latest = date('Y-m-d', $file->latestTimestamp());
            $text .= "| {$file->name} | `{$file->identifier}` | {$file->sizeFormatted()} | {$file->type()->value} | {$earliest} to {$latest} |\n";
        }

        $text .= "\n_Use `get_error_summary` with a specific file identifier for detailed level counts and recent errors._\n";

        return Response::text($text);
    }
}
