<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class SocialController extends Controller
{
    public function redirectToProvider($provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    public function handleProviderCallback($provider)
    {
        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (\Exception $e) {
            return redirect('/la/auth/index')->withErrors(['login' => 'Ошибка авторизации через соцсеть.']);
        }

        // 1. Ищем пользователя по Email в нашей базе
        $user = User::where('e_mail', $socialUser->getEmail())->first();

        if ($user) {
            // Привязываем данные соцсети, если еще не привязаны
            if (!$user->social_id) {
                $user->update([
                    'social_id' => $socialUser->getId(),
                    'social_type' => $provider,
                    'social_avatar' => $socialUser->getAvatar(),
                ]);
            }

            // Авторизуем в Laravel
            Auth::login($user);

            // 2. Синхронизируем legacy сессии (как в Ctr__Auth.php)
            $_SESSION['sh_login'] = $user->login;
            $_SESSION['sh_password'] = $user->password;
            $_SESSION['sh_id'] = $user->id;
            $_SESSION['sh_name'] = $user->name;
            $_SESSION['agency_id'] = $user->agency_id;

            if ($user->agency) {
                $_SESSION['ucaption'] = $user->agency->caption;
            }

            return redirect()->intended('/la/index/index');
        }

        return redirect('/la/auth/index')->withErrors([
            'login' => 'Пользователь с Email ' . $socialUser->getEmail() . ' не найден в системе. Обратитесь к администратору для регистрации.'
        ]);
    }
}
