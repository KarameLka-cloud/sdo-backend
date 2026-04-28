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

        $startDate = $plan->start_date->copy();
        $totalWorkDays = $this->resolveTotalWorkDays();
        $tasksPerDay = 3;
        $createdWorkDays = 0;
        $currentDate = $startDate->copy();
        $dayOffset = 0;

        while ($createdWorkDays < $totalWorkDays) {
            if ($this->isWorkingDayForSchedule($plan->work_schedule, $dayOffset, $currentDate)) {
                $createdWorkDays++;

                $tasks = $this->buildTasksForDay($plan->shift, $tasksPerDay, $createdWorkDays);
                if (empty($tasks) && $this->hasTemplateBlueprint()) {
                    $currentDate->addDay();
                    $dayOffset++;
                    continue;
                }

                $day = AdaptationPlanDay::create([
                    'adaptation_plan_id' => $plan->id,
                    'work_day' => $createdWorkDays,
                    'date' => $currentDate->toDateString(),
                    'completion' => 'в процессе',
                ]);

                if (!empty($tasks)) {
                    $day->tasks()->createMany($tasks);
                }
            }

            $currentDate->addDay();
            $dayOffset++;
        }

        $this->planContext = null;
    }

    private function buildTasksForDay(int $shift, int $tasksPerDay, int $workDay): array
    {
        $blueprint = $this->normalizeBlueprint($this->getTemplateBlueprint());
        if (!empty($blueprint)) {
            $tasksForDay = array_values(array_filter(
                $blueprint,
                fn(array $item) => $this->isTaskForWorkDay($item, $workDay)
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

            return [
                'description' => (string) $item['description'],
                'responsible_role' => isset($item['responsible_role']) ? (string) $item['responsible_role'] : null,
                'day_from' => isset($item['day_from']) ? (int) $item['day_from'] : null,
                'day_to' => isset($item['day_to']) ? (int) $item['day_to'] : null,
                'links' => array_values(array_filter(array_map(
                    fn($link) => is_string($link) ? trim($link) : null,
                    $item['links'] ?? []
                ))),
            ];
        }, $blueprint)));
    }

    private function isTaskForWorkDay(array $task, int $workDay): bool
    {
        $dayFrom = $task['day_from'] ?? null;
        $dayTo = $task['day_to'] ?? null;

        if ($dayFrom === null && $dayTo === null) {
            return true;
        }

        if ($dayFrom !== null && $dayTo === null) {
            return $workDay === $dayFrom;
        }

        return $workDay >= $dayFrom && $workDay <= $dayTo;
    }

    private function resolveTotalWorkDays(): int
    {
        $blueprint = $this->normalizeBlueprint($this->getTemplateBlueprint());
        if (empty($blueprint)) {
            return 14;
        }

        $maxDay = 1;
        foreach ($blueprint as $item) {
            $dayFrom = $item['day_from'] ?? null;
            $dayTo = $item['day_to'] ?? null;
            $candidate = $dayTo ?? $dayFrom ?? 1;
            $maxDay = max($maxDay, (int) $candidate);
        }

        return $maxDay;
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
