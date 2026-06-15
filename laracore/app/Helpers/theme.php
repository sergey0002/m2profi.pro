<?php

if (!function_exists('theme_asset')) {
    /**
     * Получить URL к ресурсу темы с учетом переопределения
     */
    function theme_asset(string $path, ?string $theme = null): string
    {
        $theme = $theme ?? config('theme.default', 'default');
        
        $host = request()->getHost();
        $subdomain = explode('.', $host)[0];
        if ($subdomain === 'localhost' || $subdomain === '127') {
            $subdomain = 'em';
        }
        
        $siteDiskPath = base_path("../sites/{$subdomain}/public/themes/{$theme}/{$path}");
        $url = "";
        
        if (file_exists($siteDiskPath)) {
            return "/public/themes/{$theme}/{$path}";
        }
        
        return "/themes/{$theme}/{$path}";
    }
}

if (!function_exists('theme_view')) {
    /**
     * Получить view с учетом темы (ThemeServiceProvider уже настроил пути)
     */
    function theme_view(string $view, array $data = []): \Illuminate\View\View
    {
        return view($view, $data);
    }
}
