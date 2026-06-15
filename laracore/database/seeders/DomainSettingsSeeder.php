<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\GlobalSetting;
use Illuminate\Database\Seeder;

class DomainSettingsSeeder extends Seeder
{
    /**
     * Запуск сидера.
     */
    public function run(): void
    {
        $settings = [
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
                'value' => 'm2profi.pro',
                'label' => 'Публичный домен',
                'description' => 'Домен для внешних ссылок и ассетов.',
                'is_overridable' => false,
            ],
            [
                'module' => 'main',
                'key' => 'app_url',
                'type' => 'string',
                'value' => 'https://m2profi.pro',
                'label' => 'Основной URL',
                'description' => 'Первичный URL системы.',
                'is_overridable' => false,
            ],
            [
                'module' => 'main',
                'key' => 'public_url',
                'type' => 'string',
                'value' => 'https://m2profi.pro',
                'label' => 'Публичный URL',
                'description' => 'URL для фронтенда.',
                'is_overridable' => false,
            ],
            [
                'module' => 'main',
                'key' => 'platform_domain',
                'type' => 'string',
                'value' => 'panel.m2profi.pro.test',
                'label' => 'Домен платформы (Админ)',
                'description' => 'Домен центральной панели управления.',
                'is_overridable' => false,
            ],
        ];

        foreach ($settings as $setting) {
            GlobalSetting::updateOrCreate(
                ['module' => $setting['module'], 'key' => $setting['key']],
                $setting
            );
        }
    }
}
