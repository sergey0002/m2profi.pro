<?php

namespace App\Http\Controllers\Core;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Ctr__Auth extends Controller
{
    /**
     * Показать форму входа
     */
    public function act__index()
    {
        // Если уже авторизован - редирект на главную
        if (Auth::check()) {
            return redirect('/la/index/index');
        }

        return view('auth.login');
    }

    /**
     * Обработка входа
     */
    public function act__login(Request $request)
    {
        $credentials = $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ]);

        $login = $credentials['login'];
        $password = $credentials['password'];

        // Ищем пользователя по логину
        $user = \App\Models\User::where('login', $login)->first();

        if ($user) {
            $isValid = false;
            
            // Проверка пароля: если bcrypt хеш, используем Hash::check
            if (str_starts_with($user->password, '$2y$')) {
                $isValid = \Illuminate\Support\Facades\Hash::check($password, $user->password);
            } else {
                // Иначе простое сравнение (legacy plaintext)
                $isValid = ($user->password === $password);
            }

            if ($isValid) {
                Auth::login($user);
                $request->session()->regenerate();
                
                // Синхронизируем legacy сессии для совместимости
                $_SESSION['sh_login'] = $user->login;
                $_SESSION['sh_password'] = $user->password;
                $_SESSION['sh_id'] = $user->id;
                $_SESSION['sh_name'] = $user->name;
                $_SESSION['agency_id'] = $user->agency_id;
                
                // Загружаем связанные данные
                if ($user->agency) {
                    $_SESSION['ucaption'] = $user->agency->caption;
                }
                
                if ($user->adminAgency) {
                    $_SESSION['adm_caption'] = $user->adminAgency->caption;
                    $_SESSION['agency_adm_id'] = $user->adminAgency->agency_id;
                }

                return redirect()->intended('/la/index/index');
            }
        }

        return back()->withErrors([
            'login' => 'Неверные Логин или Пароль!',
        ])->withInput($request->only('login'));
    }

    /**
     * Выход
     */
    public function act__logout(Request $request)
    {
        Auth::logout();
        
        // Очищаем legacy сессии
        unset($_SESSION['sh_password']);
        unset($_SESSION['sh_login']);
        unset($_SESSION['agency_id']);
        unset($_SESSION['sh_name']);
        unset($_SESSION['sh_id']);
        unset($_SESSION['adm_caption']);
        unset($_SESSION['gl_user_id']);
        unset($_SESSION['ucaption']);
        unset($_SESSION['agency_adm_id']);

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/la/auth/index');
    }
}
