<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Запуск миграции.
     * Создание таблицы локальных настроек площадки (переопределения).
     */
    public function up(): void
    {
        Schema::create('tenant_settings', function (Blueprint $table) {
            $table->id();
            $table->string('module', 50)->comment('Имя модуля');
            $table->string('key', 50)->comment('Ключ настройки');
            $table->text('value')->nullable()->comment('Переопределенное значение');
            $table->timestamps();

            $table->unique(['module', 'key'], 'idx_tenant_module_key_unique');
        });
    }

    /**
     * Откат миграции.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_settings');
    }
};
