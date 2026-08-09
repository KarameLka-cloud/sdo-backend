<?php

use App\Models\Mentorship\AdaptationPlan;
use App\Services\Mentorship\AdaptationPlanStructureGenerator;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

Artisan::command('adaptation:backfill-days {--plan-id=}', function (
    AdaptationPlanStructureGenerator $generator
) {
    $planId = $this->option('plan-id');
    $processed = 0;
    $generated = 0;

    $query = AdaptationPlan::query()
        ->withCount('days')
        ->orderBy('id');

    if ($planId !== null) {
        $query->where('id', (int) $planId);
    }

    $query->chunkById(100, function ($plans) use ($generator, &$processed, &$generated) {
        foreach ($plans as $plan) {
            $processed++;

            if ($plan->days_count > 0) {
                continue;
            }

            DB::transaction(function () use ($generator, $plan) {
                $generator->generate($plan);
            });

            $generated++;
            $this->line("Generated days/tasks for plan #{$plan->id}");
        }
    });

    $this->info("Backfill complete. Processed: {$processed}, generated: {$generated}");
})->purpose('Generate adaptation days/tasks for existing plans');
