<?php

namespace App\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

class TestTool extends Tool
{
    /**
     * The tool's description.
     */
    protected string $description = <<<'MARKDOWN'
        Simple ping tool to check if the Laravel MCP server is alive.
    MARKDOWN;

    public function name(): string
    {
        return 'ping';
    }

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        return Response::text('Pong! Laravel MCP is working.');
    }

    /**
     * Get the tool's input schema.
     *
     * @return array<string, \Illuminate\Contracts\JsonSchema\JsonSchema>
     */
    public function schema(JsonSchema $schema): array
    {
        return [];
    }
}
