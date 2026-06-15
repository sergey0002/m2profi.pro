<?php

use Laravel\Mcp\Facades\Mcp;
use App\Mcp\Servers\McpServer;

Mcp::local('default', McpServer::class);
// Mcp::web('/mcp/demo', \App\Mcp\Servers\PublicServer::class);
