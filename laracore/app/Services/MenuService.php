<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;

class MenuService
{
    /**
     * Получить структуру меню для текущего пользователя
     */
    public function getMenuItems(): array
    {
        $user = Auth::user();
        
        if (!$user) {
            return [];
        }

        $login = $user->login;
        $agencyId = $user->agency_id;
        $admCaption = session('adm_caption');
        
        $menu = [];
        
        // 1. Квартиры (все пользователи)
        $menu[] = [
            'title' => 'Квартиры',
            'url' => '/sahmatka/user.php?action=objects',
            'icon' => 'menu-icon-1.svg',
            'active' => request()->get('action') === 'objects',
        ];
        
        // 2. Парковки (если есть доступ)
        if ($this->checkAccess('parking') || true) {
            $menu[] = [
                'title' => 'Парковки',
                'icon' => 'carp.png',
                'submenu' => $this->getParkingSubmenu($login),
                'active' => in_array(request()->get('ctr'), ['parking', 'parking_buildings', 'parking_areas', 'parking_floors', 'parking_spaces', 'parking_broni']),
            ];
        }
        
        // 3. Коммерческие помещения
        if (in_array($login, ['admin', 'fd', 'demo_admin'])) {
            $menu[] = [
                'title' => 'Коммерческие помещения',
                'icon' => 'menu-icon-7.svg',
                'submenu' => $this->getCommercialSubmenu($login),
                'active' => in_array(request()->get('ctr'), ['rentobjects', 'renthomes', 'rentbroni']),
            ];
        } else {
            // Упрощенное меню для обычных пользователей
            $menu[] = [
                'title' => 'Коммерческие помещения',
                'icon' => 'menu-icon-7.svg',
                'submenu' => [
                    ['title' => 'Аренда', 'url' => '/sahmatka/ctrind.php?ctr=rentobjects&act=index_ag'],
                    ['title' => 'Продажа', 'url' => '/sahmatka/ctrind.php?ctr=rentobjects&act=index_ag&sale=1'],
                ],
                'active' => in_array(request()->get('ctr'), ['rentobjects', 'renthomes', 'rentbroni']),
            ];
        }
        
        // 4. Брони (все кроме определенных пользователей)
        if (!in_array($login, ['keys1', 'keys2', 'em_nsv', 'director'])) {
            $menu[] = [
                'title' => 'Брони',
                'url' => '/sahmatka/user.php?action=show_broni',
                'icon' => 'menu-icon-2.svg',
                'active' => request()->get('action') === 'show_broni',
            ];
        }
        
        // 5. Статистика (только для админов)
        if (in_array($login, ['admin', 'director', 'fd', 'demo_admin'])) {
            $menu[] = [
                'title' => 'Статистика',
                'icon' => 'menu-icon-3.svg',
                'submenu' => $this->getStatisticsSubmenu($login),
                'active' => in_array(request()->get('action'), ['stat_salen', 'stat_sale', 'agency_stat', 'object_stat', 'stat_salen2']) 
                         || in_array(request()->get('ctr'), ['parking_stat', 'stat_econom', 'stat_sales_dynamic']),
            ];
        }
        
        // 6. Пользователи (для админов агентств)
        // Проверяем adm_caption через сессию или через связь (если будет настроена)
        // В legacy использовалась $_SESSION['adm_caption']
        if ($login !== 'admin' && $admCaption && $login !== 'demo_admin') {
            $menu[] = [
                'title' => 'Пользователи',
                'url' => '/sahmatka/user.php?action=users',
                'icon' => 'menu-icon-3.svg',
                'active' => request()->get('action') === 'users',
            ];
        }

        // 6.1 Пользователи NEW (админы и админы агентств)
        if ($user->isSuperAdmin() || $user->isAgencyAdmin()) {
            $menu[] = [
                'title' => 'Пользователи NEW',
                'url' => route('users.index'),
                'icon' => 'menu-icon-3.svg',
                'active' => request()->is('la/users*'),
            ];
        }
        
        /* 
        // 7. Агентства - УДАЛЕНО: Перенесено в панель управления
        if (in_array($login, ['admin', 'fd', 'demo_admin'])) {
            $menu[] = [
                'title' => 'Агентства',
                'url' => '/sahmatka/ctrind.php?ctr=agency',
                'icon' => 'menu-icon-5.svg',
                'active' => request()->get('ctr') === 'agency',
            ];
        }

        // 7.1 Агентства NEW (только Super Admin) - УДАЛЕНО
        if ($user->isSuperAdmin()) {
            $menu[] = [
                'title' => 'Агентства NEW',
                'url' => route('agencies.index'),
                'icon' => 'menu-icon-5.svg',
                'active' => request()->is('la/agencies*'),
            ];
        }
        */
        
        // 8. Экскурсии
        $excursionItem = $this->getExcursionsMenuItem($login, $agencyId);
        if ($excursionItem) {
            $menu[] = $excursionItem;
        }
        
        // 9. Выдача ключей
        $keysItem = $this->getKeysMenuItem($login, $agencyId);
        if ($keysItem) {
            $menu[] = $keysItem;
        }
        
        // 10. Заявки с сайта
        if (in_array($login, ['admin', 'fd', 'demo_admin'])) {
            $menu[] = [
                'title' => 'Заявки<br>с сайта',
                'url' => '/sahmatka/user.php?action=messages',
                'icon' => 'menu-icon-8.svg',
                'active' => request()->get('action') === 'messages',
            ];
        }
        
        // 11. Шоурум и Контакты (для не-админов)
        if ($login !== 'admin' || $login === 'demo_admin') {
            $menu[] = [
                'title' => 'Шоурум',
                'url' => '/sahmatka/user.php?action=showroom',
                'icon' => 'menu-icon-6.svg',
                'active' => request()->get('action') === 'showroom',
            ];
            
            $menu[] = [
                'title' => 'Контакты',
                'url' => '/sahmatka/user.php?action=contact',
                'icon' => 'menu-icon-8.svg',
                'active' => request()->get('action') === 'contact',
            ];
        }
        
        // 12. Настройки (только для админов)
        if (in_array($login, ['admin', 'demo_admin'])) {
            $menu[] = [
                'title' => 'Настройки',
                'icon' => 'menu-icon-6.svg',
                'submenu' => [
                    ['title' => 'Настройки объектов', 'url' => '/sahmatka/ctrind.php?ctr=homeseditor'],
                    ['title' => 'Настройки ЖК', 'url' => '/sahmatka/ctrind.php?ctr=homes_kvartal'],
                ],
                'active' => in_array(request()->get('ctr'), ['homeseditor', 'homes_kvartal']),
            ];
        }

        // 12.1 Настройки NEW
        if (in_array($login, ['admin', 'demo_admin'])) {
            $menu[] = [
                'title' => 'Настройки <span style="background:#e74c3c; color:#fff; padding:1px 4px; border-radius:3px; font-size:9px;">NEW</span>',
                'url' => route('settings.index'),
                'icon' => 'menu-icon-6.svg', // Используем ту же иконку или другую подходящую
                'active' => request()->is('la/settings*'),
            ];
        }
        
        // 13. Документы агентств
        if (in_array($login, ['admin', 'demo_admin', 'docm'])) {
            $menu[] = [
                'title' => 'Документы агентств',
                'url' => '/sahmatka/ctrind.php?ctr=agfiles&act=index',
                'icon' => 'menu-icon-9.svg',
                'active' => request()->get('ctr') === 'agfiles',
            ];
        }
        
        // 14. Документы
        if ($login === 'admin') {
            $menu[] = [
                'title' => 'Документы',
                'url' => '/sahmatka/user.php?action=docs',
                'icon' => 'menu-icon-9.svg',
                'active' => request()->get('action') === 'docs',
            ];
        } else {
            // Скрытый пункт для остальных
            $menu[] = [
                'title' => 'Документы',
                'url' => '/sahmatka/user.php?action=docs',
                'icon' => 'menu-icon-9.svg',
                'active' => request()->get('action') === 'docs',
                'hidden' => true,
            ];
        }
        
        // 15. Архив статистики
        if (in_array($login, ['admin', 'demo_admin', 'docm'])) {
            $menu[] = [
                'title' => 'Архив статистики',
                'url' => '/sahmatka/ctrind.php?ctr=stat_econom_arh',
                'icon' => 'menu-icon-9.svg',
                'active' => request()->get('ctr') === 'stat_econom_arh',
            ];
        }
        
        return array_filter($menu); // Убираем пустые элементы
    }
    
    /**
     * Проверка доступа (заглушка для fw_check_access)
     */
    protected function checkAccess(string $permission): bool
    {
        // TODO: Реализовать проверку прав доступа
        return true;
    }
    
    /**
     * Подменю для парковок
     */
    protected function getParkingSubmenu(string $login): array
    {
        $submenu = [
            ['title' => 'Каталог', 'url' => '/sahmatka/ctrind.php?ctr=parking_floors&act=catalog'],
        ];
        
        if (in_array($login, ['admin', 'demo_admin'])) {
            $submenu[] = ['title' => 'Брони парковок', 'url' => '/sahmatka/ctrind.php?ctr=parking_broni&act=index'];
            $submenu[] = ['title' => 'Здания', 'url' => '/sahmatka/ctrind.php?ctr=parking_buildings'];
            $submenu[] = ['title' => 'Поэтажные планы', 'url' => '/sahmatka/ctrind.php?ctr=parking_floors'];
            $submenu[] = ['title' => 'Парковочные места', 'url' => '/sahmatka/ctrind.php?ctr=parking_spaces'];
        }
        
        return $submenu;
    }
    
    /**
     * Подменю для коммерческих помещений
     */
    protected function getCommercialSubmenu(string $login): array
    {
        $submenu = [
            ['title' => 'Аренда', 'url' => '/sahmatka/ctrind.php?ctr=rentobjects&act=index_ag'],
            ['title' => 'Продажа', 'url' => '/sahmatka/ctrind.php?ctr=rentobjects&act=index_ag&sale=1'],
        ];
        
        if (in_array($login, ['admin', 'demo_admin'])) {
            $submenu[] = ['title' => 'Здания', 'url' => '/sahmatka/ctrind.php?ctr=renthomes'];
            $submenu[] = ['title' => 'Помещения', 'url' => '/sahmatka/ctrind.php?ctr=rentobjects'];
            $submenu[] = ['title' => 'Брони', 'url' => '/sahmatka/ctrind.php?ctr=rentbroni&act=index'];
        }
        
        return $submenu;
    }
    
    /**
     * Подменю для статистики
     */
    protected function getStatisticsSubmenu(string $login): array
    {
        $submenu = [
            ['title' => 'Подписанные договоры', 'url' => '/sahmatka/user.php?action=stat_salen'],
            ['title' => 'Статистика продаж', 'url' => '/sahmatka/user.php?action=stat_sale'],
            ['title' => 'Статистика агентств', 'url' => '/sahmatka/user.php?action=agency_stat'],
            ['title' => 'Статистика квартир', 'url' => '/sahmatka/user.php?action=object_stat'],
            ['title' => 'Статистика парковок', 'url' => '/sahmatka/ctrind.php?ctr=parking_stat'],
            ['title' => 'Сводная статистика', 'url' => '/sahmatka/ctrind.php?ctr=stat_econom'],
            ['title' => 'Статистика продаж (NEW)', 'url' => '/sahmatka/ctrind.php?ctr=stat_sales_dynamic'],
        ];
        
        if (in_array($login, ['admin', 'director', 'fd', 'demo_admin'])) {
            $submenu[] = ['title' => 'Калькулятор наценки', 'url' => '/sahmatka/ctrind.php?ctr=econom', 'hidden' => true];
            $submenu[] = ['title' => 'Метрика', 'url' => '/sahmatka/ctrind.php?ctr=metrika', 'hidden' => true];
        }
        
        return $submenu;
    }
    
    /**
     * Пункт меню "Экскурсии"
     */
    protected function getExcursionsMenuItem(string $login, ?int $agencyId): ?array
    {
        if (in_array($login, ['admin', 'op15', 'fd', 'demo_admin'])) {
            $submenu = [
                ['title' => 'Запись на экскурсии', 'url' => '/sahmatka/user.php?action=exc_zapis'],
            ];
            
            if (in_array($login, ['admin', 'op15', 'demo_admin'])) {
                $submenu[] = ['title' => 'Редактор расписания', 'url' => '/sahmatka/user.php?action=zapis_editor'];
            }
            
            return [
                'title' => 'Экскурсии',
                'icon' => 'menu-icon-6.svg',
                'submenu' => $submenu,
                'active' => in_array(request()->get('action'), ['exc_zapis', 'zapis_editor']),
            ];
        } elseif ($agencyId == 92 && !in_array($login, ['keys1', 'keys2', 'em_nsv'])) {
            return [
                'title' => 'Экскурсии',
                'url' => '/sahmatka/user.php?action=exc_zapis',
                'icon' => 'menu-icon-6.svg',
                'active' => request()->get('action') === 'exc_zapis',
            ];
        }
        
        return null;
    }
    
    /**
     * Пункт меню "Выдача ключей"
     */
    protected function getKeysMenuItem(string $login, ?int $agencyId): ?array
    {
        if (in_array($login, ['admin', 'op15', 'fd', 'keys1', 'keys2', 'em_nsv', 'demo_admin'])) {
            $submenu = [
                ['title' => 'Запись на выдачу', 'url' => '/sahmatka/ctrind.php?ctr=zapiskeys'],
            ];
            
            if (in_array($login, ['admin', 'op15', 'demo_admin'])) {
                $submenu[] = ['title' => 'Редактор расписания', 'url' => '/sahmatka/ctrind.php?ctr=zapisx'];
            }
            
            $submenu[] = ['title' => 'Статистика', 'url' => '/sahmatka/ctrind.php?ctr=zapis_stat'];
            
            return [
                'title' => 'Выдача<br>ключей',
                'icon' => 'menu-icon-7.svg',
                'submenu' => $submenu,
                'active' => in_array(request()->get('ctr'), ['zapiskeys', 'zapisx', 'zapis_stat']),
            ];
        } elseif ($agencyId == 92) {
            return [
                'title' => 'Выдача<br>ключей',
                'url' => '/sahmatka/ctrind.php?ctr=zapiskeys',
                'icon' => 'menu-icon-7.svg',
                'active' => request()->get('ctr') === 'zapiskeys',
            ];
        }
        
        return null;
    }
}
