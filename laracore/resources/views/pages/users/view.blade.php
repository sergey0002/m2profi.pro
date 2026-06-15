@extends('layouts.app')

@section('title', 'Просмотр пользователя')

@section('page-title', 'Просмотр пользователя')

@section('content')
@php
    $user = App\Models\User::with('agency', 'roles')->findOrFail($userId);
@endphp

<div class="row">
    <div class="col-md-12">
        <div class="mb-3">
            <a href="{{ route('users.index') }}" class="btn btn-secondary">
                <i class="mdi mdi-arrow-left"></i> Назад к списку
            </a>
            @can('update', $user)
                <a href="{{ route('users.edit', $user->id) }}" class="btn btn-primary">
                    <i class="mdi mdi-pencil"></i> Редактировать
                </a>
            @endcan
        </div>
        
        <div class="card">
            <div class="card-body">
                <h3>{{ $user->name }}</h3>
                
                <table class="table table-bordered mt-3">
                    <tr>
                        <th width="200">ID</th>
                        <td>{{ $user->id }}</td>
                    </tr>
                    <tr>
                        <th>Логин</th>
                        <td>{{ $user->login }}</td>
                    </tr>
                    <tr>
                        <th>ФИО</th>
                        <td>{{ $user->name }}</td>
                    </tr>
                    <tr>
                        <th>Email</th>
                        <td>{{ $user->e_mail ?: '-' }}</td>
                    </tr>
                    <tr>
                        <th>Телефон</th>
                        <td>{{ $user->phone ?: '-' }}</td>
                    </tr>
                    @if(auth()->user()->isSuperAdmin())
                    <tr>
                        <th>Агентство</th>
                        <td>{{ $user->agency?->caption ?? '-' }}</td>
                    </tr>
                    @endif
                    <tr>
                        <th>Роли</th>
                        <td>
                            @forelse($user->roles as $role)
                                <span class="badge badge-info">{{ $role->name }}</span>
                            @empty
                                <span class="text-muted">Нет ролей</span>
                            @endforelse
                        </td>
                    </tr>
                    <tr>
                        <th>Статус</th>
                        <td>
                            @if($user->del == 1)
                                <span class="badge badge-danger">Заблокирован</span>
                            @elseif($user->agency && $user->agency->unactiv == 1)
                                <span class="badge badge-warning">Агентство заблокировано</span>
                            @else
                                <span class="badge badge-success">Активен</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Дата создания</th>
                        <td>{{ $user->add_datetime ? $user->add_datetime->format('d.m.Y H:i') : '-' }}</td>
                    </tr>
                </table>
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
.badge {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 500;
}
.badge-success { background-color: #28a745; color: white; }
.badge-danger { background-color: #dc3545; color: white; }
.badge-warning { background-color: #ffc107; color: #212529; }
.badge-info { background-color: #17a2b8; color: white; }
</style>
@endpush
@endsection
