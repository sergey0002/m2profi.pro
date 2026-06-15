<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tenant;

class TenantUpdatePassword extends Command
{
    protected $signature = 'tenant:update-password {subdomain} {password}';
    protected $description = 'Update tenant database password';

    public function handle()
    {
        $subdomain = $this->argument('subdomain');
        $password = $this->argument('password');

        $tenant = Tenant::where('subdomain', $subdomain)->first();
        if (!$tenant) {
            $this->error("Tenant '{$subdomain}' not found.");
            return 1;
        }

        $tenant->db_password = encrypt($password);
        $tenant->save();

        $this->info("Password updated for tenant '{$subdomain}'.");
        return 0;
    }
}
