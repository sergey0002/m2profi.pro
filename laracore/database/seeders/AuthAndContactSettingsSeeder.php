<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\GlobalSetting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AuthAndContactSettingsSeeder extends Seeder
{
    /**
     * Запуск сидера.
     */
    public function run(): void
    {
        $settings = [
            // Доменные настройки (из предыдущего шага, для синхронизации с панелью)
            [
                'module' => 'main',
                'key' => 'app_domain',
                'type' => 'string',
                'value' => 'm2profi.pro',
                'label' => 'Основной домен системы',
                'description' => 'Корневой домен для клиентских площадок.',
                'is_overridable' => false,
            ],
            [
                'module' => 'main',
                'key' => 'public_domain',
                'type' => 'string',
                'value' => 'em-nsk.ru',
                'label' => 'Публичный домен',
                'description' => 'Основной сайт компании.',
                'is_overridable' => false,
            ],
            [
                'module' => 'main',
                'key' => 'platform_domain',
                'type' => 'string',
                'value' => 'panel.m2profi.pro.test',
                'label' => 'Домен панели управления',
                'description' => 'Адрес административной части.',
                'is_overridable' => false,
            ],

            // Контакты менеджера
            [
                'module' => 'main',
                'key' => 'manager_name',
                'type' => 'string',
                'value' => 'Татьяна Чечушкова',
                'label' => 'ФИО менеджера (техподдержка)',
                'description' => 'Отображается на странице входа.',
                'is_overridable' => true,
            ],
            [
                'module' => 'main',
                'key' => 'manager_phone',
                'type' => 'string',
                'value' => '+7 953 869 72-47',
                'label' => 'Телефон менеджера',
                'description' => 'Телефон или WhatsApp для связи.',
                'is_overridable' => true,
            ],
            [
                'module' => 'main',
                'key' => 'manager_whatsapp',
                'type' => 'string',
                'value' => '79538697247',
                'label' => 'WhatsApp менеджера (только цифры)',
                'description' => 'Используется для формирования ссылки wa.me.',
                'is_overridable' => true,
            ],
            [
                'module' => 'main',
                'key' => 'support_email',
                'type' => 'string',
                'value' => 'op-an@em-nsk.group',
                'label' => 'Email техподдержки',
                'description' => 'Отображается в контактах и используется для уведомлений.',
                'is_overridable' => true,
            ],
            [
                'module' => 'main',
                'key' => 'noreply_email',
                'type' => 'string',
                'value' => 'noreply@em-nsk.ru',
                'label' => 'Email (noreply)',
                'description' => 'Адрес отправителя для системных писем.',
                'is_overridable' => false,
            ],

            // Настройки авторизации
            [
                'module' => 'auth',
                'key' => 'allow_registration',
                'type' => 'boolean',
                'value' => 'true',
                'label' => 'Разрешить регистрацию',
                'description' => 'Позволяет пользователям самостоятельно регистрироваться.',
                'is_overridable' => true,
            ],
            [
                'module' => 'auth',
                'key' => 'allow_social_login',
                'type' => 'boolean',
                'value' => 'true',
                'label' => 'Вход через соц. сети',
                'description' => 'Отображать кнопки входа через VK, Yandex и др.',
                'is_overridable' => true,
            ],

            // Блок для агентств
            [
                'module' => 'auth',
                'key' => 'show_agency_block',
                'type' => 'boolean',
                'value' => 'true',
                'label' => 'Показывать блок "Агентствам"',
                'description' => 'Отображает информационный блок для АН на странице входа.',
                'is_overridable' => true,
            ],
            [
                'module' => 'auth',
                'key' => 'agency_regulations_url',
                'type' => 'string',
                'value' => 'https://em-nsk.ru/reg/',
                'label' => 'Ссылка на регламент АН',
                'description' => 'URL страницы с документами для сотрудничества.',
                'is_overridable' => true,
            ],
        ];

        // 1. Обновляем global_settings (для Laracore)
        foreach ($settings as $setting) {
            GlobalSetting::updateOrCreate(
                ['module' => $setting['module'], 'key' => $setting['key']],
                $setting
            );
        }

        // Получаем ID модулей
        $moduleIds = DB::connection('central')->table('modules')->pluck('id', 'slug');

        // 2. Обновляем setting_definitions (для Панели)
        // Используем DB::connection('central') для гарантии записи в нужную базу
        foreach ($settings as $setting) {
            $moduleId = $moduleIds[$setting['module']] ?? 6; // Fallback to 6 (Main)

            DB::connection('central')->table('setting_definitions')->updateOrInsert(
                ['key' => $setting['key']],
                [
                    'module' => $setting['module'],
                    'name' => $setting['label'],
                    'type' => $setting['type'] === 'boolean' ? 'bool' : $setting['type'],
                    'default_value' => $setting['value'],
                    'description' => $setting['description'],
                    'is_global' => !($setting['is_overridable'] ?? false),
                    'module_id' => $moduleId,
                    'updated_at' => now(),
                ]
            );
        }
    }
}
