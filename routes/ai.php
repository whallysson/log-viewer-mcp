<?php

use Laravel\Mcp\Facades\Mcp;
use Whallysson\LogViewerMcp\Servers\LogViewerServer;

Mcp::local('log-viewer', LogViewerServer::class);
