<?php

namespace App\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

class HelloTool extends Tool
{
    public function name(): string
    {
        return 'hello';
    }

    public function description(): string
    {
        return 'Say hello to someone.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'name' => [
                    'type' => 'string',
                    'description' => 'The name of the person to greet.',
                ],
            ],
            'required' => ['name'],
        ];
    }

    public function handle(Request $request): Response
    {
        $name = $request->get('name');
        return Response::text("Hello, {$name}!");
    }
}
