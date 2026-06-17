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
        Schema::table('education_webinars', function (Blueprint $table) {
            $table->string('link')->default('')->after('title');
            $table->renameColumn('time_start', 'time');
        });

        Schema::table('education_webinars', function (Blueprint $table) {
            $table->dropColumn('time_end');
            $table->unsignedInteger('duration')->default(1)->after('date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('education_webinars', function (Blueprint $table) {
            $table->dropColumn('duration');
            $table->time('time_end')->after('time');
        });

        Schema::table('education_webinars', function (Blueprint $table) {
            $table->renameColumn('time', 'time_start');
            $table->dropColumn('link');
        });
    }
};
