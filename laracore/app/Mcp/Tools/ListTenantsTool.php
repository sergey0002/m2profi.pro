<?php

namespace App\Mcp\Tools;

use App\Models\Tenant;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

class ListTenantsTool extends Tool
{
    /**
     * The tool's description.
     */
    protected string $description = 'List all registered tenants in the system.';

    public function name(): string
    {
        return 'list-tenants';
    }

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $tenants = Tenant::all(['id', 'name', 'subdomain', 'status']);
        
        if ($tenants->isEmpty()) {
            return Response::text('No tenants found.');
        }

        $output = "Total tenants: " . $tenants->count() . "\n\n";
        foreach ($tenants as $tenant) {
            $output .= "- ID: {$tenant->id} | Name: {$tenant->name} | Subdomain: {$tenant->subdomain} | Status: {$tenant->status}\n";
        }

        return Response::text($output);
    }

    /**
     * Get the tool's input schema.
     */
    public function schema(JsonSchema $schema): array
    {
        return [];
    }
}
