@extends('layouts.auth')

@section('content')
<div class="login-modal">
    <div class="row">
        <div class="col login-modal-col-main">
            <div class="login-modal-main" style="max-width: 600px;">
                <a href="{{ url('/la/auth/index') }}" class="login-modal__linkback">Вернуться к входу</a>
                
                <div class="login-modal-form" style="margin: 3vh 0 5vh;">
                    <div class="login-modal-form__logo">
                        <img src="https://m2profi.pro/images/logo.svg" alt="">
                    </div>
                    <div class="login-modal-form__title">Регистрация агентства</div>
                    <div class="login-modal-form__subtitle">Заполните анкету для начала сотрудничества</div>
                    
                    @if (session('success'))
                        <div class="alert alert-success" style="background: #e6fcf5; color: #0ca678; padding: 20px; border-radius: 8px; margin-bottom: 30px; text-align: center; border: 1px solid #0ca678;">
                            {{ session('success') }}
                            <div style="margin-top: 15px;">
                                <a href="{{ url('/la/auth/index') }}" class="btn" style="background: #0ca678; color: #fff; padding: 10px 25px; border-radius: 6px; text-decoration: none; display: inline-block;">На страницу входа</a>
                            </div>
                        </div>
                    @else
                        @if ($errors->any())
                            <div class="alert alert-danger" style="color: red; margin-bottom: 20px;">
                                <ul style="margin: 0; padding-left: 20px;">
                                    @foreach ($errors->all() as $error)
                                        <li>{!! $error !!}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form action="{{ route('register-agency') }}" method="post" enctype="multipart/form-data">
                            @csrf
                            <div style="margin-bottom: 15px;">
                                <label style="display: block; font-size: 13px; color: #666; margin-bottom: 5px;">Название организации *</label>
                                <input type="text" name="caption" value="{{ old('caption') }}" required placeholder="ООО или ИП">
                            </div>

                            <div style="display: flex; gap: 15px; margin-bottom: 15px;">
                                <div style="flex: 1;">
                                    <label style="display: block; font-size: 13px; color: #666; margin-bottom: 5px;">ИНН *</label>
                                    <input type="text" name="inn" value="{{ old('inn') }}" required>
                                </div>
                                <div style="flex: 1;">
                                    <label style="display: block; font-size: 13px; color: #666; margin-bottom: 5px;">ФИО руководителя *</label>
                                    <input type="text" name="admin_name" value="{{ old('admin_name') }}" required>
                                </div>
                            </div>

                            <div style="display: flex; gap: 15px; margin-bottom: 15px;">
                                <div style="flex: 1;">
                                    <label style="display: block; font-size: 13px; color: #666; margin-bottom: 5px;">E-mail *</label>
                                    <input type="email" name="admin_email" value="{{ old('admin_email') }}" required>
                                </div>
                                <div style="flex: 1;">
                                    <label style="display: block; font-size: 13px; color: #666; margin-bottom: 5px;">Телефон *</label>
                                    <input type="text" name="admin_phone" value="{{ old('admin_phone') }}" required>
                                </div>
                            </div>

                            <div style="margin-bottom: 15px;">
                                <label style="display: block; font-size: 13px; color: #666; margin-bottom: 5px;">Карточка организации *</label>
                                <input type="file" name="organization_card" required style="border: none; padding: 5px 0;">
                            </div>

                            <div style="margin-bottom: 25px;">
                                <label style="display: block; font-size: 13px; color: #666; margin-bottom: 5px;">Бланк с печатью *</label>
                                <input type="file" name="email_form" required style="border: none; padding: 5px 0;">
                            </div>

                            <button class="login-modal-form__btn btn" type="submit">Отправить заявку<i></i></button>
                        </form>

                        <div style="margin-top: 25px; text-align: center; color: #666; font-size: 14px;">
                            Уже зарегистрированы? <a href="{{ url('/la/auth/index') }}" style="color: #00CDAD;">Войти</a>
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
                    <div class="login-modal__title">РЕГИСТРАЦИЯ ПАРТНЕРА</div>
                    <div class="login-modal__text">
                        Присоединяйтесь к нашей профессиональной сети. <br>
                        После отправки заявки наш менеджер свяжется с вами для подтверждения данных.
                    </div>
                    <div class="login-modal__contact">
                        <div class="login-modal__contact-item">Техподдержка: <a href="mailto:support@m2profi.pro">support@m2profi.pro</a></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
