<?php

namespace App\Services;

use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

class TenantService
{
    /**
     * Получить текущего тенанта по поддомену
     */
    public function getCurrentTenant(?string $subdomain = null): ?Tenant
    {
        if (!$subdomain) {
            $subdomain = explode('.', request()->getHost())[0];
        }

        return Tenant::where('subdomain', $subdomain)
            ->where('status', 'active')
            ->first();
    }
    
    /**
     * Проверить, включен ли модуль у тенанта
     */
    public function hasModule(string $subdomain, string $moduleSlug): bool
    {
        $tenant = $this->getCurrentTenant($subdomain);
        
        if (!$tenant) {
            return false;
        }
        
        return $tenant->hasModule($moduleSlug);
    }
    
    /**
     * Получить настройки модуля для тенанта
     */
    public function getModuleSettings(string $subdomain, string $moduleSlug): ?array
    {
        $tenant = $this->getCurrentTenant($subdomain);
        
        if (!$tenant) {
            return null;
        }
        
        $module = $tenant->modules()
            ->where('module_slug', $moduleSlug)
            ->wherePivot('is_enabled', true)
            ->first();
        
        return $module ? json_decode($module->pivot->settings, true) : null;
    }
    
    /**
     * Подключиться к БД тенанта
     */
    public function connectToTenantDatabase(Tenant $tenant): void
    {
        $config = $tenant->getDatabaseConfig();
        
        // Устанавливаем соединение 'tenant'
        Config::set('database.connections.tenant', $config);
        
        // Очищаем кэш соединений, чтобы Laravel переподключился
        DB::purge('tenant');
        
        // Устанавливаем дефолтное соединение на 'tenant'
        Config::set('database.default', 'tenant');
        
        // Также устанавливаем параметры 'central' для доступа к главной базе, если нужно
        // (Оно уже должно быть настроено в config/database.php)
    }
}
