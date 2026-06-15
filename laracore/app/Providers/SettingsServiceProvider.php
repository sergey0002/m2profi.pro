<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Config;

class SettingsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Проверяем наличие таблицы настроек, чтобы избежать ошибок при миграциях
        try {
            if (Schema::hasTable('global_settings')) {
                // Переопределяем конфигурацию Laravel значениями из БД
                // Домены и URL
                Config::set('domains.app_domain', get_setting('main', 'app_domain', config('domains.app_domain')));
                Config::set('domains.public_domain', get_setting('main', 'public_domain', config('domains.public_domain')));
                Config::set('domains.app_url', get_setting('main', 'app_url', config('domains.app_url')));
                Config::set('domains.public_url', get_setting('main', 'public_url', config('domains.public_url')));
                Config::set('domains.platform_domain', get_setting('main', 'platform_domain', config('domains.platform_domain')));

                // Системные настройки Laravel
                Config::set('app.url', config('domains.app_url'));
                
                // Email и адрес отправителя
                Config::set('mail.from.address', get_setting('main', 'admin_email', config('mail.from.address')));
            }
        } catch (\Exception $e) {
            // В случае отсутствия БД или других ошибок - используем значения из .env
        }
    }
}
