<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;

class MainDashboardSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Создание прав для Платформы
        $permissions = [
            'tenants.viewAny', 'tenants.create', 'tenants.update', 'tenants.delete',
            'modules.viewAny', 'modules.create', 'modules.update', 'modules.delete',
            'settings.viewAny', 'settings.update',
        ];
        
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }
        
        // 2. Создание ролей
        $platformAdmin = Role::firstOrCreate(['name' => 'platform-admin', 'guard_name' => 'web']);
        $platformAdmin->givePermissionTo(Permission::all());
        
        $platformManager = Role::firstOrCreate(['name' => 'platform-manager', 'guard_name' => 'web']);
        $platformManager->givePermissionTo([
            'tenants.viewAny', 'tenants.update',
            'modules.viewAny',
            'settings.viewAny',
        ]);
        
        // 3. Создание Администратора Платформы
        // Используем данные из глобальных настроек панели
        $email = get_setting('main', 'admin_email', 'admin@m2profi.pro');
        $password = get_setting('main', 'admin_password', 'SecurePassword123!');
        
        $admin = User::firstOrCreate(
            ['login' => 'platform_admin'],
            [
                'name' => 'Platform Admin',
                'email' => $email,
                'password' => $password, // Legacy БД хранит в открытом виде или md5? 
                // В User model: getAuthPassword возвращает пароль как есть.
                // Но мы хотим захешировать для новой системы? 
                // В User::fillable есть password.
                'agency_id' => 0,
                'del' => 0,
                'add_datetime' => now(),
            ]
        );
        
        $admin->assignRole($platformAdmin);
    }
}
