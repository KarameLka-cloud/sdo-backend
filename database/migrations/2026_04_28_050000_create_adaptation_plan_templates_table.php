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
    }

    public function down(): void
    {
        Schema::dropIfExists('adaptation_plan_templates');
    }
};
