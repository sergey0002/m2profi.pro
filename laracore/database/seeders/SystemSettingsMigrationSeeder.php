<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\GlobalSetting;

class SystemSettingsMigrationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            [
                'module' => 'main',
                'key' => 'platform_domain',
                'type' => 'string',
                'value' => env('PLATFORM_DOMAIN', 'mine.m2profi.pro.test'),
                'label' => 'Домен платформы (Filament)',
                'description' => 'Домен, на котором работает основная панель управления.',
                'is_overridable' => false,
            ],
            [
                'module' => 'main',
                'key' => 'admin_password',
                'type' => 'string',
                'value' => env('ADMIN_PASSWORD', 'SecurePassword123!'),
                'label' => 'Пароль администратора по умолчанию',
                'description' => 'Используется сидерами для создания platform_admin.',
                'is_overridable' => false,
            ],
            [
                'module' => 'main',
                'key' => 'app_url',
                'type' => 'string',
                'value' => env('APP_URL', 'https://mine.m2profi.pro.test'),
                'label' => 'Базовый URL приложения',
                'description' => 'Используется для генерации абсолютных ссылок.',
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
