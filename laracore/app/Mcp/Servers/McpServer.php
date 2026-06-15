<?php

namespace App\Mcp\Servers;

use Laravel\Mcp\Server;
use App\Mcp\Tools\HelloTool;
use App\Mcp\Tools\TestTool;

class McpServer extends Server
{
    /**
     * The MCP server's name.
     */
    protected string $name = 'Laravel default Server';

    /**
     * The MCP server's version.
     */
    protected string $version = '0.0.1';

    /**
     * The MCP server's instructions for the LLM.
     */
    protected string $instructions = <<<'MARKDOWN'
        Standard Laravel MCP Server providing access to application tools.
    MARKDOWN;

    /**
     * The tools registered with this MCP server.
     *
     * @var array<int, class-string<\Laravel\Mcp\Server\Tool>>
     */
    protected array $tools = [
        HelloTool::class,
        TestTool::class,
    ];
}
