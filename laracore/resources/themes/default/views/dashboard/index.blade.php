@extends('layouts.app')

@section('title', 'Личный кабинет - M2 Profi')

@section('page-title', 'Личный кабинет')

@section('content')
<div class="dashboard-welcome">
    <div class="welcome-card">
        <h2>Привет, {{ $user->name ?? $user->login }}!</h2>
        <p class="welcome-text">Это новая версия М2 Профи</p>
        
        <div class="user-info">
            <p><strong>Логин:</strong> {{ $user->login }}</p>
            <p><strong>ID:</strong> {{ $user->id }}</p>
            @if($user->agency)
                <p><strong>Агентство:</strong> {{ $user->agency->caption }}</p>
            @endif
        </div>
    </div>
</div>

<style>
.dashboard-welcome {
    padding: 40px 0;
}

.welcome-card {
    background: #fff;
    border-radius: 12px;
    padding: 40px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.welcome-card h2 {
    font-size: 32px;
    color: #333;
    margin-bottom: 20px;
}

.welcome-text {
    font-size: 18px;
    color: #666;
    margin-bottom: 30px;
}

.user-info {
    background: #f5f5f5;
    padding: 20px;
    border-radius: 8px;
}

.user-info p {
    margin: 10px 0;
    font-size: 16px;
}

.user-info strong {
    color: #00CDAD;
}
</style>
@endsection
