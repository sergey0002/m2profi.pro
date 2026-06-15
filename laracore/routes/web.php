<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Core\DashboardController;
use App\Http\Controllers\Core\Ctr__Auth;

// Laracore теперь обслуживает только клиентские площадки (Tenants).
// Глобальное администрирование вынесено в /sites/panel.

Route::middleware(['web'])->group(function () {
    
    // Auth Routes - только для клиентов
    Route::get('/la/auth/index', [Ctr__Auth::class, 'act__index'])->name('login');
    Route::get('/la/auth/logout', [Ctr__Auth::class, 'act__logout'])->name('auth.logout');
    Route::post('/la/auth/login', [Ctr__Auth::class, 'act__login'])->name('auth.login');

    // Socialite Routes
    Route::get('/la/auth/social/{provider}', 'App\Http\Controllers\Auth\SocialController@redirectToProvider')->name('auth.social');
    Route::get('/la/auth/social/{provider}/callback', 'App\Http\Controllers\Auth\SocialController@handleProviderCallback');

    // Agency Registration (Регистрация новой площадки)
    Route::get('/la/auth/register-agency', 'App\Http\Controllers\Auth\AgencyRegistrationController@showRegistrationForm')->name('register-agency');
    Route::post('/la/auth/register-agency', 'App\Http\Controllers\Auth\AgencyRegistrationController@register');
    
    // Password Recovery
    Route::get('/la/auth/forgot-password', 'App\Http\Controllers\Auth\ForgotPasswordController@showLinkRequestForm')->name('password.request');
    Route::post('/la/auth/forgot-password', 'App\Http\Controllers\Auth\ForgotPasswordController@sendResetLinkEmail')->name('password.email');

    // Protected Routes (Личный кабинет площадки)
    Route::middleware(['auth'])->prefix('la')->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard.home');
        Route::get('/index', [DashboardController::class, 'index']);
        Route::get('/index/index', [DashboardController::class, 'index'])->name('dashboard.index');
        
        // Users Management (Управление пользователями ТЕКУЩЕЙ площадки)
        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/', [App\Http\Controllers\La\UsersController::class, 'index'])->name('index');
            Route::get('/create', [App\Http\Controllers\La\UsersController::class, 'create'])->name('create');
            Route::get('/{id}', [App\Http\Controllers\La\UsersController::class, 'view'])->name('view');
            Route::get('/{id}/edit', [App\Http\Controllers\La\UsersController::class, 'edit'])->name('edit');
            Route::put('/{id}', [App\Http\Controllers\La\UsersController::class, 'update'])->name('update');
        });
        
        // Settings Management (Локальные переопределения настроек)
        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('/', [App\Http\Controllers\SettingsController::class, 'index'])->name('index');
            Route::post('/update', [App\Http\Controllers\SettingsController::class, 'update'])->name('update');
        });
    });

    // Legacy Handler для клиентских доменов (подгрузка контроллеров из sites/{subdomain}/la/Controllers/ или Core)
    $legacyHandler = function ($ctr = 'index', $act = 'index', $p1 = null, $fromLa = false) {
        $subdomain = request()->attributes->get('tenant_subdomain');
        
        $ctrName = "Ctr__" . str_replace(' ', '', ucwords(str_replace(['_', '-'], ' ', $ctr)));
        $actName = "act__" . $act;

        \Illuminate\Support\Facades\Log::info("Router: matching {$ctr}/{$act} to {$ctrName}@{$actName} for domain [{$subdomain}] (Direct: $fromLa)");

        $siteControllerClass = "Sites\\{$subdomain}\\La\\Controllers\\{$ctrName}";
        $coreControllerClass = "App\\Http\\Controllers\\Core\\{$ctrName}";

        if (class_exists($siteControllerClass)) {
            $controllerClass = $siteControllerClass;
        } elseif (class_exists($coreControllerClass)) {
            $controllerClass = $coreControllerClass;
        } else {
            if ($ctr === 'index' && $act === 'index') {
                return view('welcome');
            }
            abort(404, "Controller {$ctrName} not found in [$subdomain]");
        }

        try {
            $controller = app($controllerClass);
            if (!method_exists($controller, $actName)) {
                abort(404, "Action {$actName} not found in {$controllerClass}");
            }
            return app()->call([$controller, $actName]);
        } catch (\Exception $e) {
            if (config('app.debug')) {
                throw $e;
            }
            abort(500, "Error executing controller");
        }
    };
    
    // Wildcard маршруты для легаси-части
    // 1. Маршруты с префиксом /la/ (пропускаем системные)
    Route::any('la/{ctr?}/{act?}/{p1?}', function ($ctr = 'index', $act = 'index', $p1 = null) use ($legacyHandler) {
        return $legacyHandler($ctr, $act, $p1, true);
    })->where([
        'ctr' => '^(?!admin|livewire)[a-zA-Z0-9_\-]+',
        'act' => '[a-zA-Z0-9_\-]+',
        'p1' => '[a-zA-Z0-9_\-]+'
    ]);

    // 2. Маршруты в корне (пропускаем la, admin, livewire)
    Route::any('{ctr?}/{act?}/{p1?}', function ($ctr = 'index', $act = 'index', $p1 = null) use ($legacyHandler) {
        if ($ctr === 'la') {
            return $legacyHandler($act, $p1 ?? 'index', 'index', true);
        }
        return $legacyHandler($ctr, $act, $p1, false);
    })->where([
        'ctr' => '^(?!la|admin|livewire)[a-zA-Z0-9_\-]+',
        'act' => '[a-zA-Z0-9_\-]+',
        'p1' => '[a-zA-Z0-9_\-]+'
    ]);
});
