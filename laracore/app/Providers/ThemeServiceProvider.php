<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class ThemeServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if (app()->runningInConsole()) {
            return;
        }

        $subdomain = $this->getSubdomain();
        $theme = config('theme.default', 'default');

        // 1. Проверяем переопределение темы на уровне сайта
        $siteThemePath = base_path(str_replace(
            '{subdomain}',
            $subdomain,
            config('theme.site_themes_path')
        ) . "/{$theme}/views");

        if (is_dir($siteThemePath)) {
            View::prependLocation($siteThemePath);
        }

        // 2. Добавляем путь к теме ядра
        $coreThemePath = config('theme.core_path') . "/{$theme}/views";
        if (is_dir($coreThemePath)) {
            View::prependLocation($coreThemePath);
        }

        // 3. Делаем переменные темы доступными во всех views
        View::share('theme', $theme);
        View::share('subdomain', $subdomain);
    }

    protected function getSubdomain(): string
    {
        $host = request()->getHost();
        $subdomain = explode('.', $host)[0];
        
        if ($subdomain === 'localhost' || $subdomain === '127') {
            return 'em';
        }
        
        return $subdomain;
    }

    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../../config/theme.php', 'theme'
        );
    }
}
