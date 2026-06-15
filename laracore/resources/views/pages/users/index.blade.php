@extends('layouts.app')

@section('title', 'Управление пользователями')

@section('page-title', 'Пользователи')

@section('content')
@php
    $sort = request('sort', 'id');
    $dir = request('dir', 'desc');
    $nextDir = $dir === 'asc' ? 'desc' : 'asc';

    $user = auth()->user();
    $query = App\Models\User::with('agency', 'roles');
    
    if (!$user->isSuperAdmin()) {
        if ($user->isAgencyAdmin()) {
            $query->where('agency_id', $user->agency_id);
        } else {
            $query->where('id', $user->id);
        }
    }
    
    if ($search = request('search')) {
        $query->where(function($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('login', 'like', "%{$search}%")
                ->orWhere('e_mail', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%")
                ->orWhereHas('agency', function($sq) use ($search) {
                    $sq->where('caption', 'like', "%{$search}%");
                });
        });
    }
    
    if ($role = request('role')) {
        $query->role($role);
    }

    // Сортировка
    $sortMap = [
        'id' => 'id',
        'login' => 'login',
        'name' => 'name',
        'email' => 'e_mail',
        'phone' => 'phone',
        'status' => 'del'
    ];
    
    $orderBy = $sortMap[$sort] ?? 'id';
    $query->orderBy($orderBy, $dir);
    
    $users = $query->get();

    function sortUrl($field, $currentSort, $currentDir, $nextDir) {
        $params = request()->all();
        $params['sort'] = $field;
        $params['dir'] = ($currentSort === $field) ? $nextDir : 'asc';
        return request()->fullUrlWithQuery($params);
    }

    function sortClass($field, $currentSort, $currentDir) {
        if ($currentSort !== $field) return '';
        return $currentDir === 'asc' ? 'sort_asc' : 'sort_desc';
    }
@endphp

<div class="stat">
    <div class="stat-top stat-top_lp">
        <form method="GET" action="{{ route('users.index') }}" id="filtrform" style="width:100%;">
            <div class="stat-top-filter" style="display: flex; align-items: flex-end;">
                <div class="filter-item"> 
                    <span class="input_title">Поиск</span>
                    <input type="text" id="search" name="search" class="input_edit" value="{{ request('search') }}" placeholder="">	
                </div>

                <div class="stat-top-item stat-top-select" style="margin-left: 15px;">
                    <span class="input_title">Роль</span>
                    <select name="role" class="input_edit" style="padding: 5px;">
                        <option value="">Все роли</option>
                        @foreach(\Spatie\Permission\Models\Role::all() as $role)
                            <option value="{{ $role->name }}" {{ request('role') === $role->name ? 'selected' : '' }}>{{ $role->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="stat-top-item" style="margin-left: 15px;">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="mdi mdi-magnify"></i> Найти
                    </button>
                </div>
            </div>
        </form>

        <div style="display: flex; align-items: center; margin-top: 10px;">
            <div class="filter-item filter-item_print"> 
                <a href="JavaScript:window.print();" class="filter-item-icon">
                    <img src="{{ theme_asset('images/print.svg') }}" width="32">
                </a>
            </div>
            <div class="filter-item filter-item_print"> 
                <a href="#" title="Excel" class="filter-item-icon">
                    <img src="{{ theme_asset('images/excel.svg') }}" width="32">
                </a>
            </div>
            <div class="filter-item filter-item_print"> 
                <a href="#" title="PDF" class="filter-item-icon">
                    <img src="{{ theme_asset('images/pdf.svg') }}" width="32">
                </a>
            </div>
            @can('create', App\Models\User::class)
            <div class="filter-item filter-item_print"> 
                <a href="{{ route('users.create') }}" class="filter-item-icon">
                    <img src="{{ theme_asset('images/add.svg') }}" width="32">
                </a>
            </div>
            @endcan
        </div>
    </div>

    <div class="stat-table stat-table_notpd stat-table-user table">
        <table>
            <thead>
                <tr>
                    <th><a href="{{ sortUrl('id', $sort, $dir, $nextDir) }}" class="{{ sortClass('id', $sort, $dir) }}"><b>id</b></a></th>
                    <th><a href="{{ sortUrl('login', $sort, $dir, $nextDir) }}" class="{{ sortClass('login', $sort, $dir) }}"><b>Логин</b></a></th>
                    <th><a href="{{ sortUrl('name', $sort, $dir, $nextDir) }}" class="{{ sortClass('name', $sort, $dir) }}"><b>ФИО</b></a></th>
                    <th><a href="{{ sortUrl('email', $sort, $dir, $nextDir) }}" class="{{ sortClass('email', $sort, $dir) }}"><b>Email</b></a></th>
                    <th><a href="{{ sortUrl('phone', $sort, $dir, $nextDir) }}" class="{{ sortClass('phone', $sort, $dir) }}"><b>Телефон</b></a></th>
                    @if(auth()->user()->isSuperAdmin())
                        <th><b>Агентство</b></th>
                    @endif
                    <th><b>Роли</b></th>
                    <th><a href="{{ sortUrl('status', $sort, $dir, $nextDir) }}" class="{{ sortClass('status', $sort, $dir) }}"><b>Статус</b></a></th>
                    <th style="min-width:70px;"> </th>
                </tr>
            </thead>
            <tbody id="fw_data_tbody">
                @forelse($users as $u)
                    <tr>
                        <td><span>{{ $u->id }}</span></td>
                        <td><span>{{ $u->login }}</span></td>
                        <td><span>{{ $u->name }}</span></td>
                        <td><span>{{ $u->e_mail ?: '-' }}</span></td>
                        <td><span>{{ $u->phone ?: '-' }}</span></td>
                        @if(auth()->user()->isSuperAdmin())
                            <td><span>{{ $u->agency?->caption ?? '-' }}</span></td>
                        @endif
                        <td>
                            @foreach($u->roles as $role)
                                <span class="badge badge-info" style="font-size: 10px;">{{ $role->name }}</span>
                            @endforeach
                        </td>
                        <td>
                            @if($u->del == 1)
                                <span style="color:red;">Блок</span>
                            @else
                                <span style="color:green;">Ок</span>
                            @endif
                        </td>
                        <td style="wordwrap:nowrap;">
                            <a href="{{ route('users.view', $u->id) }}" style="color:#0000ff; font-size: 18px; " title="Просмотр">i</a>
                            &nbsp;&nbsp;
                            <a href="{{ route('users.edit', $u->id) }}" style="color:green; " class="table-edit" title="Редактировать"></a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ auth()->user()->isSuperAdmin() ? 9 : 8 }}" class="text-center">Нет пользователей</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
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
.table-edit {
    display: inline-block;
    width: 20px;
    height: 20px;
    background: url({{ theme_asset('images/edit.svg') }}) center no-repeat;
    background-size: contain;
    vertical-align: middle;
}
.badge-info { background-color: #17a2b8; color: white; padding: 2px 5px; border-radius: 3px; }

/* Сортировка */
.table table tr th a {
    position: relative;
    display: inline-block;
    padding-right: 18px;
    text-decoration: none;
    color: inherit;
}

.table table tr th a:after {
    content: "";
    position: absolute;
    right: 0;
    top: 50%;
    margin-top: -6px;
    border: 5px solid transparent;
    border-bottom-color: #ccc;
}

.table table tr th a:before {
    content: "";
    position: absolute;
    right: 0;
    top: 50%;
    margin-top: 2px;
    border: 5px solid transparent;
    border-top-color: #ccc;
}

.table table tr th a.sort_asc:after {
    border-bottom-color: #000;
}

.table table tr th a.sort_desc:before {
    border-top-color: #000;
}

.table table tr th a.sort_asc:before,
.table table tr th a.sort_desc:after {
    display: none;
}
</style>
@endpush
@endsection
