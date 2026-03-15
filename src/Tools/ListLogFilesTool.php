<?php

namespace Whallysson\LogViewerMcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;
use Opcodes\LogViewer\Facades\LogViewer;

class ListLogFilesTool extends Tool
{
    protected string $name = 'list_log_files';

    protected string $description = 'List available log files with metadata (name, size, date range, type). Use this first to discover which log files exist before searching or reading logs.';

    public function schema(JsonSchema $schema): array
    {
        return [
            'folder' => $schema->string()
                ->description('Optional folder identifier to filter files. Omit to list all files.'),
        ];
    }

    public function handle(Request $request): Response
    {
        $folder = $request->get('folder');

        if ($folder) {
            $folderObj = LogViewer::getFolder($folder);
            if (! $folderObj) {
                return Response::text("Folder not found: {$folder}");
            }
            $files = $folderObj->files();
        } else {
            $files = LogViewer::getFiles();
        }

        if ($files->isEmpty()) {
            return Response::text('No log files found.');
        }

        $text = 'Found '.count($files)." log file(s):\n\n";

        foreach ($files as $file) {
            $earliest = date('Y-m-d H:i:s', $file->earliestTimestamp());
            $latest = date('Y-m-d H:i:s', $file->latestTimestamp());

            $text .= "- **{$file->name}** ({$file->sizeFormatted()}, {$file->type()->value})\n";
            $text .= "  Identifier: `{$file->identifier}`\n";
            $text .= "  Period: {$earliest} to {$latest}\n";
            $text .= "  Path: {$file->displayPath}\n\n";
        }

        return Response::text($text);
    }
}
