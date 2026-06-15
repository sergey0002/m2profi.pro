@extends('layouts.app')

@section('title', 'Создание пользователя')

@section('page-title', 'Создание пользователя')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="mdi mdi-information"></i>
                    <strong>Форма создания пользователя</strong>
                    <p class="mb-0">Здесь будет Livewire форма для создания пользователя.</p>
                </div>
                
                <a href="{{ route('users.index') }}" class="btn btn-secondary">
                    <i class="mdi mdi-arrow-left"></i> Назад к списку
                </a>
            </div>
        </div>
    </div>
</div>
@push('head-styles')
<style>
.btn i {
    background: none !important;
    width: auto !important;
    height: auto !important;
    margin-left: 0 !important;
    display: inline-block !important;
}
.card {
    background: rgba(255, 255, 255, 0.95);
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}
</style>
@endpush
@endsection
