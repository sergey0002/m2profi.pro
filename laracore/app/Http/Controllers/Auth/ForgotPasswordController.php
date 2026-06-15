<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Mail\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ForgotPasswordController extends Controller
{
    public function showLinkRequestForm()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('e_mail', $request->email)->where('del', 0)->first();

        if (!$user) {
            return back()->withErrors(['email' => 'Пользователь с таким email не найден.']);
        }

        // Проверка блокировки агентства
        if ($user->agency && $user->agency->unactiv == 1) {
            return back()->withErrors(['email' => 'Ваше агентство заблокировано. Пожалуйста, свяжитесь с администратором.']);
        }

        // Генерация нового пароля (по условию плана)
        $newPassword = Str::random(10);
        
        // В legacy БД пароли в открытом виде
        $user->update(['password' => $newPassword]);

        // Отправка email
        try {
            Mail::to($user->e_mail)->send(new PasswordReset($user, $newPassword));
            return back()->with('status', 'Новый пароль был отправлен на ваш email.');
        } catch (\Exception $e) {
            return back()->withErrors(['email' => 'Ошибка при отправке письма: ' . $e->getMessage()]);
        }
    }
}
