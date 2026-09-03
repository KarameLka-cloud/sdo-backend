<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('adaptation_plan_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('work_schedule');
            $table->json('shifts');
            $table->json('task_blueprint')->nullable();
            $table->timestamps();
        });

        // Linked here rather than in the plans migration because the target
        // table has to exist before the foreign key can be created.
        Schema::table('adaptation_plans', function (Blueprint $table) {
            $table
                ->foreignId('adaptation_plan_template_id')
                ->nullable()
                ->after('user_id')
                ->constrained('adaptation_plan_templates')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('adaptation_plans', function (Blueprint $table) {
            $table->dropConstrainedForeignId('adaptation_plan_template_id');
        });

        Schema::dropIfExists('adaptation_plan_templates');
    }
};
