<?php

namespace App\Http\Controllers\Core;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class Ctr__Index extends Controller
{
    /**
     * Главная страница админки
     */
    public function act__index()
    {
        // Если не авторизован - редирект на вход
        if (!Auth::check()) {
            return redirect('/la/auth/index');
        }

        return view('dashboard.index');
    }
}
