@extends('layouts.auth')

@section('content')
<div class="login-modal">
    <div class="row">
        <div class="col login-modal-col-main">
            <div class="login-modal-main">
                <a href="https://em-nsk.ru/" class="login-modal__linkback">Вернуться на сайт</a>
                
                @if(get_setting('auth', 'show_agency_block', true))
                <div style="background: #FFF; padding: 30px; position:relative; margin:5vh 0 0;">
                    <div class="login-modal-form__title" style="font-size: 18px;">Агентствам недвижимости</div>
                    <div>Ознакомтесь с регламентом и списком</div>
                    <a href="{{ get_setting('auth', 'agency_regulations_url', 'https://em-nsk.ru/reg/') }}" style="color: #00CDAD; display: block; margin-top: 10px;">
                        Документов для сотрудничества &nbsp;&nbsp; 
                        <img src="https://em.m2profi.pro/rstr.png">
                    </a>
                    <img src="https://em.m2profi.pro/voskl.png" style="position:absolute; top: -30px; right: 10px;"/>
                </div>
                @endif

                <div class="login-modal-form" style="margin: 3vh 0 5vh;">
                    <div class="login-modal-form__logo">
                        <img src="https://m2profi.pro/images/logo.svg" alt="">
                    </div>
                    <div class="login-modal-form__title">Кабинет риэлтора</div>
                    <div class="login-modal-form__subtitle">Авторизация</div>
                    
                    @if ($errors->any())
                        <div class="alert alert-danger" style="color: red; margin-bottom: 20px;">
                            @foreach ($errors->all() as $error)
                                <p>{{ $error }}</p>
                            @endforeach
                        </div>
                    @endif

                    <form action="{{ url('/la/auth/login') }}" method="post">
                        @csrf
                        <input type="text" placeholder="Логин" name="login" value="{{ old('login') }}" required>
                        <input id="password-input2" type="password" name="password" placeholder="Пароль" required>
                        
                        <div class="login-modal-form__password" style="display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <input type="checkbox" id="password-check" class="password-checkbox" style="display:inline-block; width:auto; height:auto; margin: 5px;">
                                <label for="password-check" class="login-modal-form__password-label">Показать пароль</label>
                            </div>
                            <a href="{{ route('password.request') }}" style="color: #666; font-size: 13px;">Забыли пароль?</a>
                        </div>
                        
                        <input type="hidden" name="submit" value="1">
                        <button class="login-modal-form__btn btn" type="submit">Войти в кабинет<i></i></button>
                    </form>

                    @if(get_setting('auth', 'allow_social_login', true))
                    {{-- Блок социальных сетей --}}
                    <div style="text-align: center; margin-top: 30px; position: relative;">
                        <span style="background: white; padding: 0 15px; color: #bbb; font-size: 11px; position: relative; z-index: 2; text-transform: uppercase;">ИЛИ ВОЙТИ ЧЕРЕЗ</span>
                        <div style="position: absolute; top: 50%; left: 0; right: 0; height: 1px; background: #eee; z-index: 1;"></div>
                    </div>

                    <div class="social-auth-compact" style="display: flex; justify-content: center; gap: 15px; margin-top: 20px;">
                        <a href="{{ route('auth.social', 'yandex') }}" title="Yandex" style="transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='translateY(0)'">
                            <img src="https://cdn-icons-png.flaticon.com/512/5969/5969165.png" alt="Yandex" style="width: 32px; height: 32px;">
                        </a>
                        <a href="{{ route('auth.social', 'vkontakte') }}" title="VK" style="transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='translateY(0)'">
                            <img src="https://cdn-icons-png.flaticon.com/512/145/145813.png" alt="VK" style="width: 32px; height: 32px;">
                        </a>
                        <a href="{{ route('auth.social', 'google') }}" title="Google" style="transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='translateY(0)'">
                            <img src="https://cdn-icons-png.flaticon.com/512/300/300221.png" alt="Google" style="width: 32px; height: 32px;">
                        </a>
                        <a href="{{ route('auth.social', 'mailru') }}" title="Mail.ru" style="transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='translateY(0)'">
                            <img src="https://cdn-icons-png.flaticon.com/512/12108/12108170.png" alt="Mail.ru" style="width: 32px; height: 32px;">
                        </a>
                    </div>
                    @endif

                    @if(get_setting('auth', 'allow_registration', true))
                    <div class="login-modal-form__login" style="margin-top: 25px; text-align: center;">
                        Еще не зарегистрированы в сервисе? <br>
                        <a href="{{ route('register-agency') }}" style="color: #00CDAD; font-weight: 600;">Регистрация агентства</a>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="col login-modal-col-pict">
            <div class="login-modal-pict">
                <div class="login-modal__caption">
                    <div class="login-modal__logo">
                        <img src="{{ theme_asset('images/logo.svg') }}" alt="">
                    </div>
                    <div class="login-modal__title">ДОБРО ПОЖАЛОВАТЬ В СИСТЕМУ</div>
                    <div class="login-modal__text">Специалист по работе с партнерами: <br> {{ get_setting('main', 'manager_name', 'Татьяна Чечушкова') }}</div>
                    <div class="login-modal__contact">
                        <div class="login-modal__contact-item">Тел./whatsapp: <a href="https://wa.me/{{ get_setting('main', 'manager_whatsapp', '79538697247') }}">{{ get_setting('main', 'manager_phone', '+7 953 869 72-47') }}</a></div>
                        <div class="login-modal__contact-item">E-Mail: <a href="mailto:{{ get_setting('main', 'support_email', 'op-an@em-nsk.group') }}">{{ get_setting('main', 'support_email', 'op-an@em-nsk.group') }}</a></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$('body').on('click', '.password-checkbox', function(){
    if ($('#password-input2').attr('type')=='password') {
        $('#password-input2').attr('type', 'text');
    } else {
        $('#password-input2').attr('type', 'password');
    }
}); 
</script>
@endpush
@endsection
