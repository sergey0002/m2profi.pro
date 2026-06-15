@extends('layouts.app')

@section('title', 'Настройки системы - M2 Profi')
@section('page-title', 'Настройки системы')

@section('content')
<div class="stat">
    <div class="stat-top">
        <div class="admfiltr" style="display:table-cell; width: 100%;">
            <div class="filter-item" style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div>
                    <span class="input_title">Управление параметрами площадки</span>
                    <p style="font-size: 13px; color: #777; margin-top: 8px; line-height: 1.5;">
                        Ниже приведен список доступных настроек. Глобальные параметры, отмеченные иконкой <span class="material-icons" style="font-size: 14px; vertical-align: middle;">lock</span>, 
                        задаются в центральной панели управления и не могут быть изменены здесь.
                    </p>
                </div>
                <div style="margin-top: 5px;">
                    <button id="save-all-btn" class="btn" style="background: #5c7cfa; color: white; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer; display: flex; align-items: center; gap: 8px; font-weight: 600;">
                        <span class="material-icons">save</span>
                        Сохранить все изменения
                    </button>
                    <div id="save-all-status" style="margin-top: 5px; font-size: 12px; text-align: right; display: none;"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="stat-table stat-table-user stat-table_notpd table">
        <table class="dtable" id="settings-table">
            <thead>
                <tr class="dtable">
                    <th border="0" class="dtable" style="width: 150px;">Модуль</th>
                    <th border="0" class="dtable" style="width: 250px;">Настройка</th>
                    <th border="0" class="dtable" style="width: 300px;">Значение</th>
                    <th border="0" class="dtable">Описание</th>
                </tr>
            </thead>
            <tbody>
                @foreach($globalSettings as $module => $settings)
                    @foreach($settings as $setting)
                        @php
                            $tenantKey = "{$module}.{$setting->key}";
                            $tenantValue = $tenantSettings[$tenantKey]->value ?? null;
                            
                            // Каноническая логика: если есть локальное переопределение и оно разрешено
                            $isOverridden = $setting->is_overridable && $tenantValue !== null;
                            $currentValue = $isOverridden ? $tenantValue : $setting->value;
                        @endphp
                        <tr class="dtable dtable_ch">
                            <td class="dtable" style="vertical-align: top;">
                                <span class="badge" style="background: #ececec; padding: 4px 8px; border-radius: 4px; font-size: 11px; text-transform: uppercase; font-weight: bold;">
                                    {{ $module }}
                                </span>
                            </td>
                            <td class="dtable" style="vertical-align: top;">
                                <div style="font-weight: 600;">{{ $setting->label }}</div>
                                <div style="font-size: 11px; color: #999; margin-top: 2px;">{{ $setting->key }}</div>
                            </td>
                            <td class="dtable" style="vertical-align: top;">
                                <div style="display: flex; align-items: center; gap: 8px; position: relative;">
                                    @if(!$setting->is_overridable)
                                        <input type="text" class="input_edit" value="{{ $currentValue }}" disabled style="background-color: #f9f9f9; color: #999; width: 100%;">
                                        <span class="material-icons" style="color: #ccc;" title="Системная настройка">lock</span>
                                    @else
                                        @if($setting->type === 'bool' || $setting->type === 'boolean')
                                            <select class="input_edit setting-input" 
                                                    data-module="{{ $module }}" 
                                                    data-key="{{ $setting->key }}"
                                                    style="width: 100%;">
                                                <option value="1" {{ $currentValue == '1' || $currentValue === true || $currentValue === 'true' ? 'selected' : '' }}>Да</option>
                                                <option value="0" {{ $currentValue == '0' || $currentValue === false || $currentValue === 'false' ? 'selected' : '' }}>Нет</option>
                                            </select>
                                        @else
                                            <input type="text" 
                                                   class="input_edit setting-input" 
                                                   value="{{ $currentValue }}"
                                                   data-module="{{ $module }}" 
                                                   data-key="{{ $setting->key }}"
                                                   placeholder="Пусто"
                                                   style="width: 100%;">
                                        @endif
                                    @endif
                                </div>
                            </td>
                            <td class="dtable" style="vertical-align: top; font-size: 12px; color: #666; font-style: italic;">
                                {{ $setting->description }}
                            </td>
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<style>
    .save-status {
        position: absolute;
        right: -25px;
        animation: fadeInOut 2s forwards;
    }
    
    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }

    .input_edit:focus {
        border-color: #5c7cfa !important;
        box-shadow: 0 0 0 3px rgba(92, 124, 250, 0.1) !important;
        outline: none;
    }

    .dtable_ch:hover {
        background-color: #fcfcfc !important;
    }
</style>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const saveBtn = document.getElementById('save-all-btn');
        const statusDiv = document.getElementById('save-all-status');
        
        saveBtn.addEventListener('click', function() {
            const settings = [];
            const inputs = document.querySelectorAll('.setting-input');
            
            inputs.forEach(input => {
                settings.push({
                    module: input.getAttribute('data-module'),
                    key: input.getAttribute('data-key'),
                    value: input.value
                });
            });

            // Визуальная индикация начала загрузки
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<span class="material-icons" style="animation: spin 2s linear infinite;">sync</span> Сохранение...';
            
            fetch("{{ route('settings.update') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ settings: settings })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    statusDiv.innerHTML = '<span style="color: #4caf50;">Настройки успешно сохранены!</span>';
                    statusDiv.style.display = 'block';
                    setTimeout(() => { statusDiv.style.display = 'none'; }, 3000);
                } else {
                    throw new Error(data.message || 'Ошибка при сохранении');
                }
            })
            .catch(err => {
                console.error('Ошибка:', err);
                statusDiv.innerHTML = '<span style="color: #f44336;">Ошибка при сохранении. Попробуйте еще раз.</span>';
                statusDiv.style.display = 'block';
            })
            .finally(() => {
                saveBtn.disabled = false;
                saveBtn.innerHTML = '<span class="material-icons">save</span> Сохранить все изменения';
            });
        });
    });
</script>
@endpush
@endsection
