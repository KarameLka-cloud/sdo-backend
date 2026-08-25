<?php

namespace App\Services\Mentorship;

use App\Enums\UserRole;
use App\Models\Mentorship\AdaptationPlan;
use App\Models\Mentorship\AdaptationPlanDay;
use App\Models\Mentorship\AdaptationPlanTask;
use App\Models\Mentorship\AdaptationPlanTemplate;
use App\Models\User\User;
use App\Services\User\RoleResolver;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AdaptationPlanService
{
    public const PLAN_LIST_RELATIONS = [
        'user.roles',
        'mentorUser.roles',
        'departmentHeadUser.roles',
        'template',
    ];

    public const PLAN_RELATIONS = [
        'user.roles',
        'mentorUser.roles',
        'departmentHeadUser.roles',
        'template',
        'days.tasks',
    ];

    public function __construct(
        private readonly AdaptationPlanStructureGenerator $structureGenerator,
        private readonly RoleResolver $roleResolver,
    ) {}

    public function create(array $validated): AdaptationPlan
    {
        $this->validateAssigneeRoles($validated);
        $template = AdaptationPlanTemplate::findOrFail($validated['adaptation_plan_template_id']);
        $this->assertShiftAllowed($template, $validated['shift']);

        try {
            $plan = DB::transaction(function () use ($validated, $template) {
                $plan = AdaptationPlan::create([
                    ...$validated,
                    'work_schedule' => $template->work_schedule,
                ]);

                $this->structureGenerator->generate($plan);

                return $plan;
            });
        } catch (QueryException $exception) {
            if ((string) $exception->getCode() === '23000') {
                throw ValidationException::withMessages([
                    'user_id' => ['План адаптации для этого пользователя уже создан.'],
                ]);
            }

            throw $exception;
        }

        return $plan->fresh(self::PLAN_RELATIONS);
    }

    public function update(AdaptationPlan $plan, array $validated): AdaptationPlan
    {
        $this->validateAssigneeRoles($validated, $plan);

        $template = null;
        if (array_key_exists('adaptation_plan_template_id', $validated)) {
            $template = AdaptationPlanTemplate::findOrFail($validated['adaptation_plan_template_id']);
            $targetShift = $validated['shift'] ?? $plan->shift;
            $this->assertShiftAllowed($template, $targetShift);
        }

        DB::transaction(function () use (&$plan, $validated, $template) {
            $needsRegeneration = false;
            $updatePayload = $validated;

            if ($template !== null) {
                $updatePayload['work_schedule'] = $template->work_schedule;

                if ((int) $updatePayload['adaptation_plan_template_id'] !== (int) $plan->adaptation_plan_template_id) {
                    $needsRegeneration = true;
                }
            }

            if (array_key_exists('shift', $updatePayload) && (int) $updatePayload['shift'] !== (int) $plan->shift) {
                $needsRegeneration = true;
            }

            if (
                array_key_exists('start_date', $updatePayload)
                && (string) $updatePayload['start_date'] !== $plan->start_date->toDateString()
            ) {
                $needsRegeneration = true;
            }

            $plan->update($updatePayload);

            if ($needsRegeneration) {
                $plan->load('template');
                $this->structureGenerator->generate($plan, true);
            }
        });

        return $plan->fresh(self::PLAN_RELATIONS);
    }

    public function updateTaskStatus(AdaptationPlanTask $task, string $status): AdaptationPlanTask
    {
        $task->update(['status' => $status]);

        return $task->fresh();
    }

    public function updateDay(AdaptationPlanDay $day, array $validated): AdaptationPlanDay
    {
        $day->update([
            'date_from' => $validated['date_from'],
            'date_to' => $validated['date_to'] ?? null,
            'completion' => $validated['completion'],
            'employee_comment' => $validated['employee_comment'] ?? null,
            'intern_comment' => $validated['intern_comment'] ?? null,
            'mentor_comment' => $validated['mentor_comment'] ?? null,
            'department_head_comment' => $validated['department_head_comment'] ?? null,
        ]);

        return $day->fresh(['tasks']);
    }

    public function updateInternComment(AdaptationPlanDay $day, ?string $comment): AdaptationPlanDay
    {
        $day->update(['intern_comment' => $comment]);

        return $day->fresh(['tasks']);
    }

    public function findOwnedDay(int $dayId, int $userId): AdaptationPlanDay
    {
        return AdaptationPlanDay::query()
            ->where('id', $dayId)
            ->whereHas('plan', fn($query) => $query->where('user_id', $userId))
            ->firstOrFail();
    }

    public function findOwnedTask(int $dayId, int $taskId, int $userId): AdaptationPlanTask
    {
        return AdaptationPlanTask::query()
            ->where('id', $taskId)
            ->where('adaptation_plan_day_id', $dayId)
            ->whereHas('day.plan', fn($query) => $query->where('user_id', $userId))
            ->firstOrFail();
    }

    public function findManagedTask(AdaptationPlan $plan, int $dayId, int $taskId): AdaptationPlanTask
    {
        return AdaptationPlanTask::query()
            ->where('id', $taskId)
            ->where('adaptation_plan_day_id', $dayId)
            ->whereHas('day', fn($query) => $query->where('adaptation_plan_id', $plan->id))
            ->firstOrFail();
    }

    public function findManagedDay(AdaptationPlan $plan, int $dayId): AdaptationPlanDay
    {
        return AdaptationPlanDay::query()
            ->where('id', $dayId)
            ->where('adaptation_plan_id', $plan->id)
            ->firstOrFail();
    }

    public function assertShiftAllowed(AdaptationPlanTemplate $template, int $shift): void
    {
        if (!in_array($shift, $template->shifts ?? [], true)) {
            throw ValidationException::withMessages([
                'shift' => ['Выбранная смена недоступна для указанного шаблона.'],
            ]);
        }
    }

    public function validateAssigneeRoles(array $payload, ?AdaptationPlan $currentPlan = null): void
    {
        $checks = [];

        $mentorChanged = array_key_exists('mentor', $payload)
            && ((int) $payload['mentor'] !== (int) $currentPlan?->mentor);
        if ($mentorChanged) {
            $checks[(int) $payload['mentor']] = [
                'field' => 'mentor',
                'role' => UserRole::MENTOR,
                'message' => 'Выбранный пользователь не является наставником.',
            ];
        }

        $departmentHeadChanged = array_key_exists('department_head', $payload)
            && ((int) $payload['department_head'] !== (int) $currentPlan?->department_head);
        if ($departmentHeadChanged) {
            $checks[(int) $payload['department_head']] = [
                'field' => 'department_head',
                'role' => UserRole::DEPARTMENT_HEAD,
                'message' => 'Выбранный пользователь не является руководителем отдела.',
            ];
        }

        if ($checks === []) {
            return;
        }

        $users = User::query()
            ->with('roles')
            ->whereIn('id', array_keys($checks))
            ->get()
            ->keyBy('id');

        $errors = [];
        foreach ($checks as $userId => $check) {
            $user = $users->get($userId);
            $resolvedRole = $user
                ? $this->roleResolver->resolve($user->role)
                : null;

            if ($resolvedRole !== $check['role']) {
                $errors[$check['field']] = [$check['message']];
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }
}
