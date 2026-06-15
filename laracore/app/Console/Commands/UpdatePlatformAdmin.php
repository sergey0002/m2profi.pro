<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UpdatePlatformAdmin extends Command
{
    protected $signature = 'user:update-platform-admin {--password=admin : The password to set}';
    protected $description = 'Update platform admin user credentials';

    public function handle()
    {
        $user = User::where('login', 'platform_admin')->first();
        
        if (!$user) {
            $this->error('Platform admin user not found!');
            return 1;
        }
        
        $password = $this->option('password');
        $user->password = Hash::make($password);
        $user->email = 'admin@m2profi.pro';
        $user->save();
        
        $this->info("Platform admin updated:");
        $this->info("Login: {$user->login}");
        $this->info("Email: {$user->email}");
        $this->info("Password: {$password}");
        
        return 0;
    }
}
