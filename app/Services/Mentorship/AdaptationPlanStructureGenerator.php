<?php

namespace App\Services\Mentorship;

use App\Enums\CompletionStatus;
use App\Enums\TaskStatus;
use App\Models\Mentorship\AdaptationPlan;
use App\Models\Mentorship\AdaptationPlanDay;
use App\Models\Mentorship\AdaptationPlanTask;
use Carbon\Carbon;

class AdaptationPlanStructureGenerator
{
    public function generate(AdaptationPlan $plan, bool $forceRegenerate = false): void
    {
        $plan->loadMissing('template');

        if (! $forceRegenerate && $plan->days()->exists()) {
            return;
        }

        if ($forceRegenerate) {
            $this->deleteStructure($plan);
        }

        $blueprint = $this->normalizeBlueprint($plan->template?->task_blueprint);

        if ($blueprint !== []) {
            $ranges = $this->resolveBlueprintRanges($blueprint);
            foreach ($ranges as $index => $range) {
                $tasks = $this->buildTasksForRange($blueprint, $range['from'], $range['to']);
                if ($tasks === []) {
                    continue;
                }

                $dateFrom = $this->resolveDateForWorkDay(
                    $plan->start_date->copy(),
                    $plan->work_schedule,
                    $range['from']
                );
                $dateTo = $range['to'] > $range['from']
                    ? $this->resolveDateForWorkDay(
                        $plan->start_date->copy(),
                        $plan->work_schedule,
                        $range['to']
                    )
                    : null;

                $day = AdaptationPlanDay::create([
                    'adaptation_plan_id' => $plan->id,
                    'work_day' => $index + 1,
                    'day_from' => $range['from'],
                    'day_to' => $range['to'],
                    'date_from' => $dateFrom->toDateString(),
                    'date_to' => $dateTo?->toDateString(),
                    'completion' => CompletionStatus::IN_PROGRESS->value,
                ]);

                $day->tasks()->createMany($tasks);
            }
        }
    }

    /** Drops generated days and tasks. Does not touch the intern user. */
    public function deleteStructure(AdaptationPlan $plan): void
    {
        $dayIds = $plan->days()->pluck('id');
        if ($dayIds->isEmpty()) {
            return;
        }

        AdaptationPlanTask::query()
            ->whereIn('adaptation_plan_day_id', $dayIds)
            ->delete();
        AdaptationPlanDay::query()
            ->whereIn('id', $dayIds)
            ->delete();
    }

    /**
     * @param  list<array<string, mixed>>  $blueprint
     * @return list<array{description: string, status: string, responsible_role: mixed, links: array}>
     */
    private function buildTasksForRange(array $blueprint, int $rangeFrom, int $rangeTo): array
    {
        $tasksForDay = array_values(array_filter(
            $blueprint,
            fn (array $item) => $this->isTaskForRange($item, $rangeFrom, $rangeTo)
        ));

        return array_map(
            fn (array $item) => [
                'description' => $item['description'],
                'status' => TaskStatus::NOT_DONE->value,
                'responsible_role' => $item['responsible_role'],
                'links' => $item['links'],
            ],
            $tasksForDay
        );
    }

    private function normalizeBlueprint(?array $blueprint): array
    {
        if (! $blueprint) {
            return [];
        }

        return array_values(array_filter(array_map(function ($item) {
            if (! is_array($item) || empty($item['description'])) {
                return null;
            }

            $dayFrom = isset($item['day_from']) ? (int) $item['day_from'] : null;
            $dayTo = isset($item['day_to']) ? (int) $item['day_to'] : null;
            if ($dayFrom === null && $dayTo !== null) {
                $dayFrom = $dayTo;
            }
            if ($dayFrom !== null && $dayTo === null) {
                $dayTo = $dayFrom;
            }

            return [
                'description' => (string) $item['description'],
                'responsible_role' => isset($item['responsible_role']) ? (string) $item['responsible_role'] : null,
                'day_from' => $dayFrom,
                'day_to' => $dayTo,
                'links' => array_values(array_filter(array_map(
                    fn ($link) => is_string($link) ? trim($link) : null,
                    $item['links'] ?? []
                ))),
            ];
        }, $blueprint)));
    }

    /**
     * @param  list<array<string, mixed>>  $blueprint
     * @return list<array{from: int, to: int}>
     */
    private function resolveBlueprintRanges(array $blueprint): array
    {
        if ($blueprint === []) {
            return [];
        }

        $ranges = [];
        foreach ($blueprint as $item) {
            $from = $item['day_from'] ?? 1;
            $to = $item['day_to'] ?? $from;
            $ranges["{$from}:{$to}"] = [
                'from' => $from,
                'to' => $to,
            ];
        }

        $resolved = array_values($ranges);
        usort($resolved, fn (array $left, array $right) => [$left['from'], $left['to']] <=> [$right['from'], $right['to']]);

        return $resolved;
    }

    private function isTaskForRange(array $task, int $rangeFrom, int $rangeTo): bool
    {
        return (int) ($task['day_from'] ?? 1) === $rangeFrom
            && (int) ($task['day_to'] ?? ($task['day_from'] ?? 1)) === $rangeTo;
    }

    private function resolveDateForWorkDay(Carbon $startDate, string $schedule, int $targetWorkDay): Carbon
    {
        $currentDate = $startDate->copy();
        $createdWorkDays = 0;
        $dayOffset = 0;

        while (true) {
            if ($this->isWorkingDayForSchedule($schedule, $dayOffset, $currentDate)) {
                $createdWorkDays++;
                if ($createdWorkDays === $targetWorkDay) {
                    return $currentDate->copy();
                }
            }

            $currentDate->addDay();
            $dayOffset++;
        }
    }

    private function isWorkingDayForSchedule(string $schedule, int $dayOffset, Carbon $date): bool
    {
        if ($schedule === '5/2') {
            return ! $date->isWeekend();
        }

        if ($schedule === '2/2') {
            $cyclePosition = $dayOffset % 4;

            return $cyclePosition < 2;
        }

        return true;
    }
}
