<div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
    <h3 class="text-sm font-bold text-gray-900 mb-2">Данные администратора из заявки:</h3>
    @php $data = $getState(); @endphp
    @if($data)
        <div class="grid grid-cols-2 gap-4 text-sm">
            <div>
                <span class="font-semibold text-gray-600">ФИО:</span> {{ $data['admin_name'] ?? '—' }}
            </div>
            <div>
                <span class="font-semibold text-gray-600">Email:</span> {{ $data['admin_email'] ?? '—' }}
            </div>
            <div>
                <span class="font-semibold text-gray-600">Телефон:</span> {{ $data['admin_phone'] ?? '—' }}
            </div>
        </div>
        
        <div class="mt-4 flex gap-4">
            @if(isset($data['org_card']))
                <a href="{{ Storage::url($data['org_card']) }}" target="_blank" class="text-primary-600 hover:underline flex items-center gap-1">
                    <x-heroicon-o-document-text class="w-4 h-4" /> Карточка организации
                </a>
            @endif
            @if(isset($data['email_form']))
                <a href="{{ Storage::url($data['email_form']) }}" target="_blank" class="text-primary-600 hover:underline flex items-center gap-1">
                    <x-heroicon-o-envelope class="w-4 h-4" /> Бланк с email
                </a>
            @endif
        </div>
    @else
        <p class="text-gray-500">Данные отсутствуют</p>
    @endif
</div>
