<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tenant;

class CreateTenant extends Command
{
    protected $signature = 'tenant:create {subdomain} {database} {--host=localhost} {--port=3306} {--username=root} {--password=}';
    protected $description = 'Create a new tenant';

    public function handle()
    {
        $subdomain = $this->argument('subdomain');
        $database = $this->argument('database');
        $host = $this->option('host');
        $port = $this->option('port');
        $username = $this->option('username');
        $password = $this->option('password');

        // Check if tenant already exists
        $existing = Tenant::where('subdomain', $subdomain)->first();
        if ($existing) {
            $this->error("Tenant with subdomain '{$subdomain}' already exists!");
            return 1;
        }

        $tenant = new Tenant();
        $tenant->subdomain = $subdomain;
        $tenant->db_name = $database;
        $tenant->db_host = $host;
        $tenant->db_port = $port;
        $tenant->db_username = $username;
        $tenant->db_password = $password ? encrypt($password) : null;
        $tenant->status = 'active';
        $tenant->save();

        $this->info("✅ Tenant created successfully!");
        $this->info("Subdomain: {$tenant->subdomain}");
        $this->info("Database: {$tenant->db_name}");
        $this->info("Host: {$tenant->db_host}:{$tenant->db_port}");
        $this->info("Username: {$tenant->db_username}");

        return 0;
    }
}
