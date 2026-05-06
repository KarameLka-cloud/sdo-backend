<?php

namespace App\Services\Mentorship;

use App\Models\Mentorship\AdaptationPlan;
use App\Models\Mentorship\AdaptationPlanDay;
use Carbon\Carbon;

class AdaptationPlanStructureGenerator
{
    public function generate(AdaptationPlan $plan, bool $forceRegenerate = false): void
    {
        $this->planContext = $plan->loadMissing('template');

        if (!$forceRegenerate && $plan->days()->exists()) {
            $this->planContext = null;
            return;
        }

        if ($forceRegenerate) {
            $plan->days()->delete();
        }

        if ($this->hasTemplateBlueprint()) {
            $ranges = $this->resolveBlueprintRanges();
            foreach ($ranges as $index => $range) {
                $tasks = $this->buildTasksForRange($range['from'], $range['to']);
                if (empty($tasks)) {
                    continue;
                }

                $day = AdaptationPlanDay::create([
                    'adaptation_plan_id' => $plan->id,
                    'work_day' => $index + 1,
                    'day_from' => $range['from'],
                    'day_to' => $range['to'],
                    'date_from' => $this->resolveDateForWorkDay($plan->start_date->copy(), $plan->work_schedule, $range['from'])->toDateString(),
                    'date_to' => null,
                    'completion' => 'в процессе',
                ]);

                $day->tasks()->createMany($tasks);
            }

            $this->planContext = null;
            return;
        }

        $tasks = $this->buildDefaultTasks($plan->shift, 3);
        $day = AdaptationPlanDay::create([
            'adaptation_plan_id' => $plan->id,
            'work_day' => 1,
            'day_from' => 1,
            'day_to' => 1,
            'date_from' => $plan->start_date->toDateString(),
            'date_to' => null,
            'completion' => 'в процессе',
        ]);
        $day->tasks()->createMany($tasks);

        $this->planContext = null;
    }

    private function buildTasksForRange(int $rangeFrom, int $rangeTo): array
    {
        $blueprint = $this->normalizeBlueprint($this->getTemplateBlueprint());
        $tasksForDay = array_values(array_filter(
            $blueprint,
            fn(array $item) => $this->isTaskForRange($item, $rangeFrom, $rangeTo)
        ));

        return array_map(
            fn(array $item) => [
                'description' => $item['description'],
                'status' => 'не выполнено',
                'responsible_role' => $item['responsible_role'],
                'links' => $item['links'],
            ],
            $tasksForDay
        );
    }

    private function buildDefaultTasks(int $shift, int $tasksPerDay): array
    {
        $roles = ['Стажер', 'Наставник', 'Сотрудник УПиПК', 'Руководитель отдела'];
        $tasks = [];
        for ($index = 1; $index <= $tasksPerDay; $index++) {
            $role = $roles[($index - 1) % count($roles)];
            $tasks[] = [
                'description' => "Задача {$index} для смены {$shift}",
                'status' => 'не выполнено',
                'responsible_role' => $role,
                'links' => [],
            ];
        }

        return $tasks;
    }

    private ?AdaptationPlan $planContext = null;

    private function getTemplateBlueprint(): ?array
    {
        return $this->planContext?->template?->task_blueprint;
    }

    private function hasTemplateBlueprint(): bool
    {
        return !empty($this->normalizeBlueprint($this->getTemplateBlueprint()));
    }

    private function normalizeBlueprint(?array $blueprint): array
    {
        if (!$blueprint) {
            return [];
        }

        return array_values(array_filter(array_map(function ($item) {
            if (!is_array($item) || empty($item['description'])) {
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
                    fn($link) => is_string($link) ? trim($link) : null,
                    $item['links'] ?? []
                ))),
            ];
        }, $blueprint)));
    }

    private function resolveBlueprintRanges(): array
    {
        $blueprint = $this->normalizeBlueprint($this->getTemplateBlueprint());
        if (empty($blueprint)) {
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
        usort($resolved, fn(array $left, array $right) => [$left['from'], $left['to']] <=> [$right['from'], $right['to']]);
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
            return !$date->isWeekend();
        }

        if ($schedule === '2/2') {
            $cyclePosition = $dayOffset % 4;
            return $cyclePosition < 2;
        }

        return true;
    }
}
