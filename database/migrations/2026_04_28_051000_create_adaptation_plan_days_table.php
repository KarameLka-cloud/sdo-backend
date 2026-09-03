<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('adaptation_plan_days', function (Blueprint $table) {
            $table->id();
            $table->foreignId('adaptation_plan_id')->constrained('adaptation_plans')->cascadeOnDelete();
            $table->unsignedSmallInteger('work_day');
            $table->unsignedSmallInteger('day_from')->nullable();
            $table->unsignedSmallInteger('day_to')->nullable();
            $table->date('date_from')->nullable();
            $table->date('date_to')->nullable();
            $table->string('completion')->default('в процессе');
            $table->text('employee_comment')->nullable();
            $table->text('intern_comment')->nullable();
            $table->text('mentor_comment')->nullable();
            $table->text('department_head_comment')->nullable();
            $table->timestamps();

            $table->unique(['adaptation_plan_id', 'work_day']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('adaptation_plan_days');
    }
};
