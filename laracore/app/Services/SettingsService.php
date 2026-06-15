<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\GlobalSetting;
use App\Models\TenantSetting;
use Illuminate\Support\Collection;

/**
 * Сервис управления настройками.
 * Обеспечивает слияние глобальных и локальный настроек с учетом прав на переопределение.
 */
class SettingsService
{
    /**
     * Кэш настроек в памяти.
     *
     * @var array<string, array<string, mixed>>
     */
    protected array $settings = [];

    /**
     * Статус загрузки настроек.
     *
     * @var bool
     */
    protected bool $isLoaded = false;

    /**
     * Загрузка всех настроек из БД.
     *
     * @param bool $force Принудительная перезагрузка
     * @return void
     */
    public function load(bool $force = false): void
    {
        if ($this->isLoaded && !$force) {
            return;
        }

        if ($force) {
            $this->settings = [];
        }

        // 1. Загружаем эталонные настройки из панели (central DB)
        $globalSettings = GlobalSetting::all();

        // 2. Загружаем переопределения площадки (tenant DB)
        // Если соединение tenant не настроено (например, в консоли), пропускаем
        $tenantSettings = collect();
        try {
            $tenantSettings = TenantSetting::all();
        } catch (\Exception $e) {
            // В случае ошибки (например, нет таблицы в БД), оставляем пустую коллекцию
        }

        $tenantMap = $tenantSettings->groupBy('module')
            ->map(fn (Collection $moduleSettings) => $moduleSettings->keyBy('key'));

        foreach ($globalSettings as $global) {
            $value = $global->value;

            // Если разрешено переопределение и есть локальное значение
            if ($global->is_overridable && isset($tenantMap[$global->module][$global->key])) {
                $value = $tenantMap[$global->module][$global->key]->value;
            }

            $this->settings[$global->module][$global->key] = $this->castValue($value, $global->type);
        }

        $this->isLoaded = true;
    }

    /**
     * Получение значения конкретной настройки.
     *
     * @param string $module Модуль
     * @param string $key Ключ
     * @param mixed $default Значение по умолчанию
     * @return mixed
     */
    public function get(string $module, string $key, mixed $default = null): mixed
    {
        if (!$this->isLoaded) {
            $this->load();
        }

        return $this->settings[$module][$key] ?? $default;
    }

    /**
     * Приведение значения к нужному типу.
     *
     * @param mixed $value Значение
     * @param string $type Тип (string, int, bool, json)
     * @return mixed
     */
    protected function castValue(mixed $value, string $type): mixed
    {
        if ($value === null) {
            return null;
        }

        return match ($type) {
            'int', 'integer' => (int) $value,
            'bool', 'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'json' => json_decode((string) $value, true),
            default => (string) $value,
        };
    }
}
