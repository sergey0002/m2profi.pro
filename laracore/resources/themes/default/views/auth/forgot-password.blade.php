@extends('layouts.auth')

@section('content')
<div class="login-modal">
    <div class="row">
        <div class="col login-modal-col-main">
            <div class="login-modal-main">
                <a href="{{ url('/la/auth/index') }}" class="login-modal__linkback">Вернуться к входу</a>
                
                <div class="login-modal-form" style="margin: 5vh 0;">
                    <div class="login-modal-form__logo">
                        <img src="https://m2profi.pro/images/logo.svg" alt="">
                    </div>
                    <div class="login-modal-form__title">Восстановление пароля</div>
                    <div class="login-modal-form__subtitle">Введите ваш email для получения нового пароля</div>
                    
                    @if (session('status'))
                        <div class="alert alert-success" style="color: green; margin-bottom: 20px; text-align: center;">
                            {{ session('status') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger" style="color: red; margin-bottom: 20px;">
                            @foreach ($errors->all() as $error)
                                <p>{!! $error !!}</p>
                            @endforeach
                        </div>
                    @endif
                    
                    <form action="{{ route('password.email') }}" method="post">
                        @csrf
                        <input type="email" placeholder="E-mail" name="email" value="{{ old('email') }}" required>
                        
                        <button class="login-modal-form__btn btn" type="submit" style="margin-top: 20px;">Сбросить пароль<i></i></button>
                    </form>

                    <div class="auth-extra-links" style="margin-top: 20px; text-align: center; font-size: 14px;">
                        <a href="{{ url('/la/auth/index') }}" style="color: #666;">Я вспомнил пароль</a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col login-modal-col-pict">
            <div class="login-modal-pict">
                <div class="login-modal__caption">
                    <div class="login-modal__logo">
                        <img src="{{ theme_asset('images/logo.svg') }}" alt="">
                    </div>
                    <div class="login-modal__title">ВОССТАНОВЛЕНИЕ ДОСТУПА</div>
                    <div class="login-modal__text">Введите адрес электронной почты, указанный при регистрации. Мы отправим вам новый пароль.</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
