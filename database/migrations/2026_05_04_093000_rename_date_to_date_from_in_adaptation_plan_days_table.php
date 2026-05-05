<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('adaptation_plan_days', function (Blueprint $table) {
            $table->date('date_from')->nullable()->after('day_to');
        });

        DB::table('adaptation_plan_days')
            ->whereNull('date_from')
            ->update([
                'date_from' => DB::raw('date'),
            ]);

        Schema::table('adaptation_plan_days', function (Blueprint $table) {
            $table->dropColumn('date');
        });
    }

    public function down(): void
    {
        Schema::table('adaptation_plan_days', function (Blueprint $table) {
            $table->date('date')->nullable()->after('day_to');
        });

        DB::table('adaptation_plan_days')
            ->whereNull('date')
            ->update([
                'date' => DB::raw('date_from'),
            ]);

        Schema::table('adaptation_plan_days', function (Blueprint $table) {
            $table->dropColumn('date_from');
        });
    }
};
