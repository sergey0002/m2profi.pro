<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Запуск миграции.
     * Создание таблицы глобальных настроек в центральной БД (Панель).
     */
    public function up(): void
    {
        Schema::create('global_settings', function (Blueprint $table) {
            $table->id();
            $table->string('module', 50)->comment('Имя модуля (например, crm, billing, site)');
            $table->string('key', 50)->comment('Ключ настройки');
            $table->string('type', 20)->comment('Тип данных: string, integer, boolean, json');
            $table->text('value')->nullable()->comment('Значение по умолчанию');
            $table->string('label')->comment('Человекопонятное название');
            $table->text('description')->nullable()->comment('Описание настройки');
            $table->boolean('is_overridable')->default(true)->comment('Разрешено ли переопределение на площадке');
            $table->timestamps();

            $table->unique(['module', 'key'], 'idx_module_key_unique');
        });
    }

    /**
     * Откат миграции.
     */
    public function down(): void
    {
        Schema::dropIfExists('global_settings');
    }
};
