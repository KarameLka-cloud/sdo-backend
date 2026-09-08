<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('learning_items', function (Blueprint $table) {
            $table->id();
            $table->string('category'); // education | edo
            $table->string('type'); // event | course | webinar | test
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('link')->nullable();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->string('note_department')->nullable();
            $table->foreignId('position_id')->nullable()->constrained('positions')->nullOnDelete();
            $table->string('note_position')->nullable();
            $table->time('time')->nullable();
            $table->date('date');
            $table->unsignedInteger('duration')->default(1);
            $table->timestamps();

            $table->index(['category', 'type', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('learning_items');
    }
};
