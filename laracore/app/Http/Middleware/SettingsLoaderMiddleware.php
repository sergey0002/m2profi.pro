<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\SettingsService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware для предварительной загрузки настроек системы.
 * Гарантирует, что все настройки загружены в память перед началом обработки запроса.
 */
class SettingsLoaderMiddleware
{
    /**
     * Обработка входящего запроса.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Принудительная загрузка всех настроек после того, как TenantDatabaseMiddleware 
        // установил соединение с базой данных арендатора.
        app(SettingsService::class)->load(true);

        return $next($request);
    }
}
