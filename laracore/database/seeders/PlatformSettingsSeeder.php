<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Module;
use App\Models\SettingDefinition;

class PlatformSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Базовые модули
        $modules = [
            [
                'slug' => 'crm',
                'name' => 'CRM (Управление клиентами)',
                'description' => 'Основной модуль для работы с базой клиентов и сделками',
                'is_active' => true,
            ],
            [
                'slug' => 'inventory',
                'name' => 'Склад (Объекты)',
                'description' => 'Учет квартир, парковок и коммерческой недвижимости',
                'is_active' => true,
            ],
            [
                'slug' => 'agencies',
                'name' => 'Агентства',
                'description' => 'Работа с внешними агентствами недвижимости',
                'is_active' => true,
            ],
            [
                'slug' => 'booking',
                'name' => 'Бронирование',
                'description' => 'Система онлайн и офлайн бронирования объектов',
                'is_active' => true,
            ],
        ];

        foreach ($modules as $module) {
            Module::updateOrCreate(['slug' => $module['slug']], $module);
        }

        // Базовые определения настроек
        $settings = [
            [
                'key' => 'site_name',
                'name' => 'Название сайта',
                'type' => 'string',
                'default_value' => 'M2 Profi',
                'is_global' => false,
            ],
            [
                'key' => 'admin_email',
                'name' => 'Email администратора',
                'type' => 'string',
                'default_value' => 'admin@example.com',
                'is_global' => false,
            ],
            [
                'key' => 'currency',
                'name' => 'Валюта',
                'type' => 'string',
                'default_value' => 'RUB',
                'is_global' => true,
            ],
        ];

        foreach ($settings as $setting) {
            SettingDefinition::updateOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
