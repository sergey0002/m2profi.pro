<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\GlobalSetting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Сидер для заполнения глобальных настроек.
 */
class GlobalSettingsSeeder extends Seeder
{
    /**
     * Запуск сидера.
     */
    public function run(): void
    {
        // Очищаем таблицу перед заполнением
        DB::connection('central')->table('global_settings')->truncate();

        // 1. Импортируем данные из актуальной таблицы setting_definitions
        try {
            $oldSettings = DB::connection('central')->table('setting_definitions')->get();
            $modules = DB::connection('central')->table('modules')->get()->keyBy('id');

            foreach ($oldSettings as $old) {
                // Используем slug модуля или 'main' если не найден
                $moduleName = $modules[$old->module_id]->slug ?? 'main';
                
                GlobalSetting::create([
                    'module' => $moduleName,
                    'key' => $old->key,
                    'type' => $old->type ?? 'string',
                    'value' => $old->default_value,
                    'label' => $old->name,
                    'description' => $old->description ?? "Системная настройка",
                    'is_overridable' => !($old->is_locked ?? false),
                ]);
            }
        } catch (\Exception $e) {
            $this->command->error("Ошибка при импорте настроек: " . $e->getMessage());
        }
    }
}
