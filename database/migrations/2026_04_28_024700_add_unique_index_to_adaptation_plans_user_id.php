<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $duplicateUserIds = DB::table('adaptation_plans')
            ->select('user_id')
            ->groupBy('user_id')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('user_id');

        if ($duplicateUserIds->isNotEmpty()) {
            $sampleUserIds = $duplicateUserIds->take(10)->implode(', ');
            throw new \RuntimeException(
                "Cannot add unique index adaptation_plans.user_id because duplicate plans exist for user_id(s): {$sampleUserIds}. " .
                'Resolve duplicates manually and run migration again.'
            );
        }

        Schema::table('adaptation_plans', function (Blueprint $table) {
            $table->unique('user_id', 'adaptation_plans_user_id_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('adaptation_plans', function (Blueprint $table) {
            $table->dropUnique('adaptation_plans_user_id_unique');
        });
    }
};
