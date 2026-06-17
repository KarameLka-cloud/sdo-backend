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
        foreach (['edo_courses', 'education_courses', 'edo_tests', 'education_tests'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->text('description')->nullable()->after('title');
            });
        }

        Schema::table('education_webinars', function (Blueprint $table) {
            $table->text('description')->nullable()->after('title');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach (['edo_courses', 'education_courses', 'edo_tests', 'education_tests'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropColumn('description');
            });
        }

        Schema::table('education_webinars', function (Blueprint $table) {
            $table->dropColumn('description');
        });
    }
};
