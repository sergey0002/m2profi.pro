<?php

namespace App\Auth;

use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable;

class LegacyUserProvider extends EloquentUserProvider
{
    /**
     * Проверка пароля для legacy системы (пароли в открытом виде)
     */
    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        $plain = $credentials['password'];
        $authPassword = $user->getAuthPassword();

        // Проверяем как хеш (если Laravel автоматически захешировал)
        $info = password_get_info($authPassword);
        if ($info['algo'] !== null) {
            return \Illuminate\Support\Facades\Hash::check($plain, $authPassword);
        }
        
        // Иначе проверяем как открытый текст (legacy)
        return $plain === $authPassword;
    }
}
