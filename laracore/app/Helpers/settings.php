<?php

declare(strict_types=1);

use App\Services\SettingsService;

if (!function_exists('get_setting')) {
    /**
     * Глобальный хелпер для получения настроек системы.
     *
     * @param string $module Модуль (например, 'crm', 'site')
     * @param string $key Ключ настройки
     * @param mixed $default Значение по умолчанию, если настройка не найдена
     * @return mixed
     */
    function get_setting(string $module, string $key, mixed $default = null): mixed
    {
        try {
            // Если приложение еще не инициализировано для резолва сервисов (например, при загрузке конфигов)
            if (!interface_exists('Illuminate\Contracts\Foundation\Application') || !app()->has(SettingsService::class)) {
                return $default;
            }
            
            return app(SettingsService::class)->get($module, $key, $default);
        } catch (\Throwable $e) {
            return $default;
        }
    }
}
