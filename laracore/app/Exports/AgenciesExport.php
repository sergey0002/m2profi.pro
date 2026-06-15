<?php

namespace App\Exports;

use App\Models\Agency;
use App\Models\UserStat;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Http\Request;

class AgenciesExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $filters;

    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }

    public function query()
    {
        $query = Agency::with(['adminUser'])
            ->withCount('users')
            ->addSelect([
                'last_activity' => UserStat::select('date')
                    ->whereIn('users_id', function($q) {
                        $q->select('id')->from('users')->whereColumn('agency_id', 'agency.agency_id');
                    })
                    ->orderBy('date', 'desc')
                    ->limit(1)
            ]);

        // Apply filters
        if (!empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('caption', 'like', "%{$search}%")
                  ->orWhere('inn', 'like', "%{$search}%")
                  ->orWhereHas('adminUser', function($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%")
                        ->orWhere('login', 'like', "%{$search}%")
                        ->orWhere('e_mail', 'like', "%{$search}%");
                  });
            });
        }

        if (isset($this->filters['type']) && $this->filters['type'] !== '') {
            $query->where('type', $this->filters['type']);
        }

        if (isset($this->filters['status']) && $this->filters['status'] !== '') {
            $query->where('registration_status', $this->filters['status']);
        }

        if (empty($this->filters['show_dell'])) {
            $query->where('del', 0);
        } else {
            $query->where('del', 1);
        }

        if (!empty($this->filters['show_block'])) {
            $query->where('unactiv', 1);
        } else {
            $query->where('unactiv', 0);
        }

        // Sorting
        $sort = $this->filters['sort'] ?? 'agency_id';
        $dir = $this->filters['dir'] ?? 'desc';
        
        $sortMap = [
            'agency_id' => 'agency.agency_id',
            'caption' => 'caption',
            'inn' => 'inn',
            'add_datetime' => 'add_datetime',
            'type' => 'type',
            'registration_status' => 'registration_status',
            'lastactiv' => 'last_activity',
            'usc' => 'users_count',
        ];

        $orderBy = $sortMap[$sort] ?? 'agency.agency_id';
        $query->orderBy($orderBy, $dir);

        return $query;
    }

    public function headings(): array
    {
        return [
            'id',
            'Регистрация',
            'Агентство',
            'Доступы (Логин / Пароль)',
            'Контакт (ФИО / Тел / Email)',
            'Активность',
            'Us',
            'Ac',
        ];
    }

    public function map($agency): array
    {
        $statusLabels = [0 => 'Активно', 1 => 'Заявка', 2 => 'Отклонено'];

        return [
            $agency->agency_id,
            $agency->add_datetime ? $agency->add_datetime->format('d.m.y H:i') : '-',
            $agency->caption . ' (ИНН: ' . $agency->inn . ')',
            $agency->adminUser ? $agency->adminUser->login . ' / ' . $agency->adminUser->password : '-',
            $agency->adminUser ? $agency->adminUser->name . ' (' . $agency->adminUser->phone . ' / ' . $agency->adminUser->e_mail . ')' : '-',
            $agency->last_activity ? \Carbon\Carbon::parse($agency->last_activity)->format('d.m.y') : '-',
            $agency->users_count,
            ($statusLabels[$agency->registration_status] ?? 'Неизвестно') . ($agency->unactiv ? ' [Блок]' : ''),
        ];
    }
}
