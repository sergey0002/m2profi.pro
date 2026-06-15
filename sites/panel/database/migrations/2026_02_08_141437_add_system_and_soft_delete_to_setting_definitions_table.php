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
        Schema::table('setting_definitions', function (Blueprint $table) {
            $table->foreignId('section_id')->nullable()->after('module_id')->constrained('setting_sections')->nullOnDelete();
            $table->boolean('is_system')->default(false)->after('is_global');
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('setting_definitions', function (Blueprint $table) {
            $table->dropForeign(['section_id']);
            $table->dropColumn(['section_id', 'is_system', 'deleted_at']);
        });
    }
};
