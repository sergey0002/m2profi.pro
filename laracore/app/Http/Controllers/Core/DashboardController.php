<?php

namespace App\Http\Controllers\Core;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Главная страница личного кабинета
     */
    public function index()
    {
        $user = Auth::user();
        
        return view('dashboard.index', [
            'user' => $user,
            'pageTitle' => 'Добро пожаловать',
        ]);
    }
}
