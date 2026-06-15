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
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('subdomain', 50)->unique()->comment('Поддомен (em, demo, msk)');
            $table->string('db_name', 100)->comment('Имя БД (m2profi_em)');
            $table->string('db_host', 100)->default('127.0.0.1');
            $table->integer('db_port')->default(3306);
            $table->string('db_username', 100)->default('root');
            $table->string('db_password', 255)->nullable();
            $table->enum('status', ['active', 'suspended', 'pending', 'deleted'])->default('active');
            $table->json('config')->nullable()->comment('Дополнительные настройки (домены, email)');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
