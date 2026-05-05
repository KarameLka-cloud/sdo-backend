<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::statement(
            'ALTER TABLE adaptation_plan_days MODIFY COLUMN date_to DATE NULL AFTER date_from'
        );
    }

    public function down(): void
    {
        DB::statement(
            'ALTER TABLE adaptation_plan_days MODIFY COLUMN date_to DATE NULL'
        );
    }
};
