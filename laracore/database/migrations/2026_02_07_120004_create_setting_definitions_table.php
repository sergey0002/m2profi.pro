<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('setting_definitions', function (Blueprint $table) {
            $table->id();
            $table->string('key', 255)->unique()->comment('Ключ настройки (module.feature.option)');
            $table->string('name', 255)->comment('Отображаемое название');
            $table->enum('type', ['string', 'integer', 'boolean', 'json', 'text', 'int', 'bool'])->default('string');
            $table->boolean('is_global')->default(false)->comment('Глобальная ли настройка');
            $table->text('default_value')->nullable();
            $table->string('module', 100)->nullable()->comment('К какому модулю относится');
            $table->boolean('is_locked')->default(false)->comment('Запрет переопределения');
            $table->boolean('is_public')->default(false)->comment('Доступна ли настройка публично');
            $table->text('description')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('setting_definitions');
    }
};
