<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\TenantService;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class TenantDatabaseMiddleware
{
    protected $tenantService;
    
    public function __construct(TenantService $tenantService)
    {
        $this->tenantService = $tenantService;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();
        $subdomain = explode('.', $host)[0];
        
        // 1. Если запрос пришел на домен платформы - Laracore не должен его обрабатывать
        // (Обычно это отсекается на уровне .htaccess, но добавим защиту здесь)
        if ($request->getHost() === config('domains.platform_domain')) {
            abort(404, 'Platform Administration moved to separate instance.');
        }
        
        // 2. Для локальной разработки, если хост localhost или 127.0.0.1
        if ($subdomain === 'localhost' || $subdomain === '127' || $host === 'em.m2profi.pro.test') {
             if ($host === 'em.m2profi.pro.test') $subdomain = 'em';
             else $subdomain = 'em'; // дефолт для локалхоста
        }

        // 3. Получаем тенанта из Central DB (через TenantService)
        // TenantService должен использовать соединение 'central' или дефолтное, если мы в Main Dashboard
        $tenant = $this->tenantService->getCurrentTenant($subdomain);
        
        
        if (!$tenant) {
            // Если тенант не найден в новой системе, пробуем старую логику или 404
            Log::warning("Tenant not found for subdomain: {$subdomain}");
            
            // Fallback: используем старую логику для обратной совместимости
            // Создаем подключение к БД m2profi_{subdomain}
            $database = "m2profi_{$subdomain}";
            \Illuminate\Support\Facades\Config::set('database.connections.tenant', [
                'driver' => 'mysql',
                'host' => env('DB_HOST', '127.0.0.1'),
                'port' => env('DB_PORT', '3306'),
                'database' => $database,
                'username' => env('DB_USERNAME', 'root'),
                'password' => env('DB_PASSWORD', 'root'),
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
            ]);
            \Illuminate\Support\Facades\DB::purge('tenant');
            \Illuminate\Support\Facades\Config::set('database.default', 'tenant');
            
            Log::info("Fallback: Connected to database {$database} for subdomain {$subdomain}");
            
            // Сохраняем subdomain в request для использования в legacy коде
            $request->attributes->set('tenant_subdomain', $subdomain);
            
            return $next($request);
        }
        
        // 4. Подключаемся к БД тенанта (если найден в таблице tenants)
        $this->tenantService->connectToTenantDatabase($tenant);
        
        // Сохраняем тенанта в контейнере для удобного доступа
        app()->instance('current.tenant', $tenant);
        
        // Сбрас Auth, чтобы он перечитал пользователя из новой БД (тенанта)
        // Иначе он будет использовать закешированного пользователя из main БД
        if (app('auth')->check()) {
            app('auth')->forgetUser();
        }

        // Сохраняем subdomain в request
        $request->attributes->set('tenant_subdomain', $subdomain);
        
        Log::info("Connected to Tenant: {$subdomain}, DB: {$tenant->db_name}");
        
        return $next($request);
    }
}
