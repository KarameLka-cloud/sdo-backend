<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('adaptation_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->foreignId('adaptation_plan_template_id')
                ->nullable()
                ->constrained('adaptation_plan_templates')
                ->nullOnDelete();
            $table->date('start_date');
            $table->string('work_schedule');
            $table->unsignedTinyInteger('shift');
            // Removing the intern removes the plan, but removing a mentor,
            // supervisor or head must not silently delete someone else's plan:
            // reassign first.
            $table->foreignId('mentor')->constrained('users')->restrictOnDelete();
            $table->foreignId('supervisor')->nullable()->constrained('users')->restrictOnDelete();
            $table->foreignId('department_head')->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('adaptation_plans');
    }
};
