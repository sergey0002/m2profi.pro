@extends('layouts.iframe')

@section('title', 'Редактирование пользователя')

@section('content')
<div class="row">
    <div class="col-md-12">
        <form id="userEditForm" action="{{ route('users.update', $user->id) }}" method="POST">
            @csrf
            @method('PUT')
            <input type="hidden" name="ajax" value="1">

            <div class="stat-top-title" style="margin-bottom: 20px;">
                <b>Редактирование пользователя: {{ $user->login }}</b>
            </div>

            <div class="form-group" style="margin-bottom: 15px;">
                <span class="input_title">ФИО</span>
                <input type="text" name="name" class="input_edit" value="{{ $user->name }}" required style="width: 100%; border: 1px solid #ddd; padding: 8px; border-radius: 4px;">
            </div>

            <div class="form-group" style="margin-bottom: 15px;">
                <span class="input_title">E-Mail</span>
                <input type="email" name="e_mail" class="input_edit" value="{{ $user->e_mail }}" style="width: 100%; border: 1px solid #ddd; padding: 8px; border-radius: 4px;">
            </div>

            <div class="form-group" style="margin-bottom: 15px;">
                <span class="input_title">Телефон</span>
                <input type="text" name="phone" class="input_edit" value="{{ $user->phone }}" style="width: 100%; border: 1px solid #ddd; padding: 8px; border-radius: 4px;">
            </div>

            <div class="form-group" style="margin-bottom: 15px;">
                <span class="input_title">Новый пароль (оставьте пустым, чтобы не менять)</span>
                <input type="text" name="password" class="input_edit" placeholder="Введите новый пароль" style="width: 100%; border: 1px solid #ddd; padding: 8px; border-radius: 4px;">
            </div>

            <div style="margin-top: 25px; display: flex; gap: 10px;">
                <button type="submit" class="btn btn-primary" style="background: #00A896; color: #fff; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer;">
                    <i class="mdi mdi-check"></i> Сохранить
                </button>
                <button type="button" class="btn btn-secondary" onclick="parent.$.magnificPopup.close()" style="background: #eee; color: #333; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer;">
                    Отмена
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    $('#userEditForm').on('submit', function(e) {
        e.preventDefault();
        const $form = $(this);
        const $btn = $form.find('button[type="submit"]');
        
        $btn.prop('disabled', true).text('Сохранение...');

        $.ajax({
            url: $form.attr('action'),
            method: 'POST',
            data: $form.serialize(),
            success: function(response) {
                if (response.success) {
                    alert(response.message);
                    parent.$.magnificPopup.close();
                } else {
                    alert('Ошибка: ' + response.message);
                    $btn.prop('disabled', false).html('<i class="mdi mdi-check"></i> Сохранить');
                }
            },
            error: function(xhr) {
                const message = xhr.responseJSON?.message || 'Произошла ошибка при сохранении';
                alert('Ошибка: ' + message);
                $btn.prop('disabled', false).html('<i class="mdi mdi-check"></i> Сохранить');
            }
        });
    });
});
</script>
@endpush
@endsection
