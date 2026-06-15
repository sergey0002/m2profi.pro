<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class LegacySessionBridge
{
    /**
     * Проверяем legacy сессию и авторизуем пользователя в Laravel
     */
    public function handle(Request $request, Closure $next)
    {
        // Пропускаем логику моста сессий для основного домена платформы (Panel)
        // и для системных запросов Livewire
        if ($request->getHost() === config('domains.platform_domain') || $request->is('la/livewire/*')) {
            return $next($request);
        }

        // Запускаем нативную PHP сессию, если она еще не запущена (для доступа к $_SESSION)
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Если пользователь уже авторизован в Laravel - пропускаем
        if (Auth::check()) {
            return $next($request);
        }

        // Проверяем legacy сессию
        if (isset($_SESSION['sh_login']) && isset($_SESSION['sh_password'])) {
            $login = $_SESSION['sh_login'];
            $password = $_SESSION['sh_password'];

            // Пытаемся найти пользователя
            $user = User::where('login', $login)
                        ->where('password', $password)
                        ->first();

            if ($user) {
                // Авторизуем в Laravel
                Auth::login($user);
                
                // Синхронизируем дополнительные данные в сессию Laravel
                session([
                    'agency_id' => $user->agency_id,
                    'sh_name' => $user->name,
                    'sh_id' => $user->id,
                ]);
            }
        } elseif (Auth::check() && !isset($_SESSION['sh_login'])) {
            // Если в Laravel авторизован, а в старой сессии данных нет — разлогиниваем в Laravel
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return $next($request);
    }
}
