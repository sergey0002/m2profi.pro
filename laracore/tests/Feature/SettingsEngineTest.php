<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\GlobalSetting;
use App\Models\TenantSetting;
use App\Services\SettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Тестирование ядра системы настроек (Settings Engine).
 */
class SettingsEngineTest extends TestCase
{
    // use RefreshDatabase; // Будем мигрировать вручную для разных подключений

    protected function setUp(): void
    {
        parent::setUp();

        // Мигрируем центральную базу данных (в памяти для sqlite)
        $this->artisan('migrate:fresh', [
            '--database' => 'central',
            '--path' => 'database/migrations/central'
        ]);

        // Мигрируем локальную базу данных (tenant)
        $this->artisan('migrate:fresh', [
            '--path' => 'database/migrations/tenant'
        ]);
        
        // Сбрасываем состояние синглтона перед каждым тестом
        $service = app(SettingsService::class);
        $this->setPrivateProperty($service, 'isLoaded', false);
        $this->setPrivateProperty($service, 'settings', []);
    }

    /**
     * Тест: Загрузка глобальных настроек из центральной БД.
     */
    public function test_it_loads_global_settings(): void
    {
        GlobalSetting::on('central')->create([
            'module' => 'site',
            'key' => 'name',
            'type' => 'string',
            'value' => 'Global M2',
            'label' => 'Название сайта',
            'is_overridable' => true
        ]);

        // Очищаем кэш в сервисе, так как он мог загрузиться пустым
        $service = app(SettingsService::class);
        $this->setPrivateProperty($service, 'isLoaded', false);
        $this->setPrivateProperty($service, 'settings', []);

        $this->assertEquals('Global M2', get_setting('site', 'name'));
    }

    /**
     * Тест: Переопределение настроек на уровне площадки (tenant).
     */
    public function test_it_overrides_settings_at_tenant_level(): void
    {
        GlobalSetting::on('central')->create([
            'module' => 'site',
            'key' => 'name',
            'type' => 'string',
            'value' => 'Global M2',
            'label' => 'Название сайта',
            'is_overridable' => true
        ]);

        TenantSetting::create([
            'module' => 'site',
            'key' => 'name',
            'value' => 'Tenant Site'
        ]);

        $service = app(SettingsService::class);
        $this->setPrivateProperty($service, 'isLoaded', false);

        $this->assertEquals('Tenant Site', get_setting('site', 'name'));
    }

    /**
     * Тест: Запрет переопределения, если is_overridable = false.
     */
    public function test_it_prevents_override_if_not_overridable(): void
    {
        GlobalSetting::on('central')->create([
            'module' => 'crm',
            'key' => 'api_key',
            'type' => 'string',
            'value' => 'SECRET_KEY',
            'label' => 'API Ключ',
            'is_overridable' => false
        ]);

        TenantSetting::create([
            'module' => 'crm',
            'key' => 'api_key',
            'value' => 'HACKED_KEY'
        ]);

        $service = app(SettingsService::class);
        $this->setPrivateProperty($service, 'isLoaded', false);

        // Должно вернуться глобальное значение
        $this->assertEquals('SECRET_KEY', get_setting('crm', 'api_key'));
    }

    /**
     * Тест: Корректная типизация значений (Casting).
     */
    public function test_it_casts_values_correctly(): void
    {
        GlobalSetting::on('central')->create([
            'module' => 'system',
            'key' => 'is_enabled',
            'type' => 'bool',
            'value' => '1',
            'label' => 'Включено',
            'is_overridable' => true
        ]);

        GlobalSetting::on('central')->create([
            'module' => 'system',
            'key' => 'max_users',
            'type' => 'int',
            'value' => '100',
            'label' => 'Макс. пользователей',
            'is_overridable' => true
        ]);

        $service = app(SettingsService::class);
        $this->setPrivateProperty($service, 'isLoaded', false);

        $this->assertSame(true, get_setting('system', 'is_enabled'));
        $this->assertSame(100, get_setting('system', 'max_users'));
    }

    /**
     * Вспомогательный метод для изменения приватных свойств (для сброса состояния синглтона).
     */
    private function setPrivateProperty(object $object, string $propertyName, mixed $value): void
    {
        $reflection = new \ReflectionClass($object);
        $property = $reflection->getProperty($propertyName);
        $property->setAccessible(true);
        $property->setValue($object, $value);
    }
}
