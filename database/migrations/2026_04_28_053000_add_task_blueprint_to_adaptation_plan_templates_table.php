<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('adaptation_plan_templates', function (Blueprint $table) {
            $table->json('task_blueprint')->nullable()->after('shifts');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('adaptation_plan_templates', function (Blueprint $table) {
            $table->dropColumn('task_blueprint');
        });
    }
};
