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
        if (!Schema::hasTable('agency')) {
            return;
        }

        Schema::table('agency', function (Blueprint $table) {
            $table->tinyInteger('registration_status')->default(0)->after('unactiv')
                ->comment('0-активно, 1-заявка, 2-отклонено');
            $table->text('registration_data')->nullable()->after('registration_status')
                ->comment('JSON данные из формы регистрации');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('agency')) {
            Schema::table('agency', function (Blueprint $table) {
                $table->dropColumn(['registration_status', 'registration_data']);
            });
        }
    }
};
