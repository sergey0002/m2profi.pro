@extends('emails.layout')

@section('title', 'Восстановление пароля')

@section('header')
    <h1>Восстановление доступа</h1>
@endsection

@section('content')
    <p>Здравствуйте, {{ $userName }}!</p>
    <p>Мы получили запрос на восстановление пароля для вашей учетной записи. Ваш пароль был успешно сброшен.</p>
    
    <div class="credentials">
        <h3>🔑 Новые данные для входа</h3>
        <div class="credential-item">
            <span class="credential-label">Логин:</span>
            <code>{{ $login }}</code>
        </div>
        <div class="credential-item">
            <span class="credential-label">Пароль:</span>
            <code>{{ $newPassword }}</code>
        </div>
    </div>
    
    <p><strong>Важно:</strong> Рекомендуем сменить этот пароль после первого входа в систему.</p>
    
    <div style="text-align: center;">
        <a href="{{ $loginUrl }}" class="btn">Войти в систему</a>
    </div>
    
    <p>Если вы не запрашивали сброс пароля, просто проигнорируйте это письмо.</p>
@endsection
