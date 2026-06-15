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
        Schema::create('modules', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 100)->unique()->comment('Уникальный идентификатор модуля');
            $table->string('name', 255)->comment('Название модуля');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true)->comment('Глобальный выключатель');
            $table->decimal('price', 10, 2)->default(0)->comment('Цена модуля (если продается)');
            $table->string('version', 20)->default('1.0.0');
            $table->json('dependencies')->nullable()->comment('Зависимости от других модулей');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('modules');
    }
};
