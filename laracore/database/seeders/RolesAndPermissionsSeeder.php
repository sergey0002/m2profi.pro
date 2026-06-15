<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        // Сброс кэша ролей и прав
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Создать права
        $permissions = [
            'users.viewAny', 'users.viewOwn', 'users.create', 'users.update', 'users.delete',
            'agencies.viewAny', 'agencies.create', 'agencies.update', 'agencies.delete',
            'agency-docs.viewAny', 'agency-docs.viewOwn', 'agency-docs.create', 'agency-docs.update', 'agency-docs.delete',
            'apartments.view', 'apartments.book',
            'broni.viewOwn', 'broni.viewAll', 'broni.updateOwn', 'broni.updateAll', 'broni.cancelAll',
            'apartments.viewStats', 'reports.viewSales'
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Создать роли и назначить права
        
        // 1. Super Admin
        $superAdmin = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
        $superAdmin->syncPermissions(Permission::all());

        // 2. Agency Admin
        $agencyAdmin = Role::firstOrCreate(['name' => 'agency-admin', 'guard_name' => 'web']);
        $agencyAdmin->syncPermissions([
            'users.viewOwn', 'users.create', 'users.update', 'users.delete',
            'agency-docs.viewOwn', 'agency-docs.create', 'agency-docs.update', 'agency-docs.delete',
            'apartments.view', 'apartments.book',
            'broni.viewOwn', 'broni.updateOwn'
        ]);

        // 3. Agent (Обычный)
        $agent = Role::firstOrCreate(['name' => 'agent', 'guard_name' => 'web']);
        $agent->syncPermissions([
            'apartments.view', 'apartments.book',
            'broni.viewOwn', 'broni.updateOwn'
        ]);

        // 4. Agent (Отдел продаж)
        $salesAgent = Role::firstOrCreate(['name' => 'sales-department-agent', 'guard_name' => 'web']);
        $salesAgent->syncPermissions([
            'apartments.view', 'apartments.book',
            'broni.viewOwn', 'broni.viewAll', 'broni.updateOwn', 'broni.updateAll', 'broni.cancelAll',
            'apartments.viewStats', 'reports.viewSales'
        ]);
    }
}
