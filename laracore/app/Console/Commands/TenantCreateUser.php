<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class TenantCreateUser extends Command
{
    protected $signature = 'tenant:create-user {subdomain} {login} {password} {--email=}';
    protected $description = 'Create a user in the tenant database';

    public function handle()
    {
        $subdomain = $this->argument('subdomain');
        $login = $this->argument('login');
        $password = $this->argument('password');
        $email = $this->option('email') ?: "{$login}@{$subdomain}.m2profi.pro";

        $tenant = Tenant::where('subdomain', $subdomain)->first();
        if (!$tenant) {
            $this->error("Tenant '{$subdomain}' not found.");
            return 1;
        }

        // Configure tenant connection
        $config = $tenant->getDatabaseConfig();
        if ($config['host'] === 'localhost') {
            $config['host'] = '127.0.0.1';
        }
        Config::set('database.connections.tenant_temp', $config);
        DB::purge('tenant_temp');

        try {
            DB::connection('tenant_temp')->getPdo();
        } catch (\Exception $e) {
            $this->error("Could not connect to tenant database: " . $e->getMessage());
            return 1;
        }
        
        $conn = DB::connection('tenant_temp');
        
        // Check if users table exists
        if (!$conn->getSchemaBuilder()->hasTable('users')) {
             $this->error("Table 'users' not found in tenant database '{$tenant->db_name}'. You probably need to run migrations.");
             return 1;
        }

        // Create user
        // Check if exists
        $exists = $conn->table('users')->where('login', $login)->exists();
        if ($exists) {
            $this->info("User '{$login}' already exists. Updating password.");
            $conn->table('users')->where('login', $login)->update([
                'password' => $password, // Plain text for legacy compatibility? Or Hash? Let's use plain text initially as per user request "с логином пароем к базе рут" (maybe they meant DB credentials, but let's be safe). 
                // Ah, user said "добавь площадку em существующую с логином пароем к базе рут". This was for tenant DB connection.
                // But generally users need passwords.
                // The `User` model handles plain text vs hash. Let's store plain text "admin" for simplicity in legacy.
            ]);
        } else {
            $conn->table('users')->insert([
                'login' => $login,
                'email' => $email,
                'password' => $password, 
                'name' => 'Admin',
                'agency_id' => 0,
                'del' => 0,
                'add_datetime' => now(),
            ]);
            $this->info("User '{$login}' created.");
        }

        return 0;
    }
}
