<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        require_once app_path('Helpers/theme.php');
        require_once app_path('Helpers/settings.php');

        $this->app->singleton(\App\Services\TenantService::class, function ($app) {
            return new \App\Services\TenantService();
        });

        $this->app->singleton(\App\Services\SettingsService::class, function ($app) {
            return new \App\Services\SettingsService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Socialite Providers Integration
        $this->app->make('Illuminate\Contracts\Events\Dispatcher')->listen(
            \SocialiteProviders\Manager\SocialiteWasCalled::class,
            [\SocialiteProviders\VKontakte\VKontakteExtendSocialite::class, 'handle'],
            [\SocialiteProviders\Yandex\YandexExtendSocialite::class, 'handle'],
            [\SocialiteProviders\MailRu\MailRuExtendSocialite::class, 'handle']
        );

        // Регистрация кастомного провайдера авторизации
        \Illuminate\Support\Facades\Auth::provider('legacy', function ($app, array $config) {
            return new \App\Auth\LegacyUserProvider($app['hash'], $config['model']);
        });


        // Настройка маршрутов Livewire для префикса /la
        \Livewire\Livewire::setScriptRoute(function ($handle) {
            return \Illuminate\Support\Facades\Route::get('/la/livewire/livewire.js', $handle);
        });
        
        \Livewire\Livewire::setUpdateRoute(function ($handle) {
            return \Illuminate\Support\Facades\Route::post('/la/livewire/update', $handle);
        });


        // Переопределение путей для шаблонов (Views) на основе поддомена
        if (!app()->runningInConsole()) {
            $host = request()->getHost();
            $subdomain = explode('.', $host)[0];
            
            // Локальный фикс для localhost
            if ($subdomain === 'localhost' || $subdomain === '127') {
                $subdomain = 'em';
            }

            // Путь к кастомным шаблонам сайта
            $customPath = base_path("../sites/{$subdomain}/la/views");

            if (is_dir($customPath)) {
                \Illuminate\Support\Facades\View::prependLocation($customPath);
            }
        }
    }
}
