<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('adaptation_plan_days', function (Blueprint $table) {
            $table->unsignedSmallInteger('day_from')->nullable()->after('work_day');
            $table->unsignedSmallInteger('day_to')->nullable()->after('day_from');
        });
    }

    public function down(): void
    {
        Schema::table('adaptation_plan_days', function (Blueprint $table) {
            $table->dropColumn(['day_from', 'day_to']);
        });
    }
};
