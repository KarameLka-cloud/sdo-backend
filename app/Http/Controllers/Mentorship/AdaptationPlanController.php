<?php

namespace App\Http\Controllers\Mentorship;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\AdaptationPlanRequest;
use App\Http\Requests\AdaptationPlanUpdateRequest;
use App\Models\Mentorship\AdaptationPlan;
use App\Models\Mentorship\AdaptationPlanDay;
use App\Models\Mentorship\AdaptationPlanTask;
use App\Models\Mentorship\AdaptationPlanTemplate;
use App\Services\Mentorship\AdaptationPlanStructureGenerator;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdaptationPlanController extends Controller
{
    public function __construct(
        private readonly AdaptationPlanStructureGenerator $structureGenerator
    ) {
    }

    public function all(): JsonResponse
    {
        if (!$this->canViewAllPlans(Auth::user()?->role)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $plans = AdaptationPlan::with(['user', 'mentorUser', 'departmentHeadUser', 'template', 'days.tasks'])
            ->orderByDesc('id')
            ->get();

        return response()->json($plans);
    }

    public function my(): JsonResponse
    {
        $authUser = Auth::user();
        $plan = AdaptationPlan::with(['user', 'mentorUser', 'departmentHeadUser', 'template', 'days.tasks'])
            ->where('user_id', $authUser?->id)
            ->orderByDesc('id')
            ->first();

        return response()->json($plan);
    }

    public function index(): JsonResponse
    {
        $authUser = Auth::user();
        $role = $authUser?->role;
        $userId = $authUser?->id;

        $query = AdaptationPlan::with(['user', 'mentorUser', 'departmentHeadUser', 'template', 'days.tasks'])->orderByDesc('id');

        if ($role === UserRole::ADMIN->value) {
            $plans = $query->get();
            return response()->json($plans);
        }

        if ($role === UserRole::MENTOR->value || $role === UserRole::DEPARTMENT_HEAD->value) {
            $plans = $query
                ->where(function ($builder) use ($userId) {
                    $builder
                        ->where('mentor', $userId)
                        ->orWhere('department_head', $userId);
                })
                ->get();

            return response()->json($plans);
        }

        $plans = $query->where('user_id', $userId)->get();
        return response()->json($plans);
    }

    public function show($id): JsonResponse
    {
        $plan = AdaptationPlan::with(['user', 'mentorUser', 'departmentHeadUser', 'template', 'days.tasks'])->findOrFail($id);
        $authUser = Auth::user();

        if (!$this->canViewPlan($plan, $authUser?->role, $authUser?->id)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        return response()->json($plan);
    }

    public function store(AdaptationPlanRequest $request): JsonResponse
    {
        if (!$this->canCreatePlan(Auth::user()?->role)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $validated = $request->validated();
        $template = AdaptationPlanTemplate::findOrFail($validated['adaptation_plan_template_id']);

        if (!in_array($validated['shift'], $template->shifts ?? [], true)) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => [
                    'shift' => ['Выбранная смена недоступна для указанного шаблона.'],
                ],
            ], 422);
        }

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
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => [
                        'user_id' => ['План адаптации для этого пользователя уже создан.'],
                    ],
                ], 422);
            }

            throw $exception;
        }

        return response()->json($plan->fresh(['user', 'mentorUser', 'departmentHeadUser', 'template', 'days.tasks']), 201);
    }

    public function update(AdaptationPlanUpdateRequest $request, $id): JsonResponse
    {
        $plan = AdaptationPlan::findOrFail($id);
        $authUser = Auth::user();

        if (!$this->canManagePlan($plan, $authUser?->role, $authUser?->id)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $validated = $request->validated();
        $needsRegeneration = false;

        if (array_key_exists('adaptation_plan_template_id', $validated)) {
            $template = AdaptationPlanTemplate::findOrFail($validated['adaptation_plan_template_id']);
            $validated['work_schedule'] = $template->work_schedule;
            $targetShift = $validated['shift'] ?? $plan->shift;

            if (!in_array($targetShift, $template->shifts ?? [], true)) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => [
                        'shift' => ['Выбранная смена недоступна для указанного шаблона.'],
                    ],
                ], 422);
            }

            if ((int) $validated['adaptation_plan_template_id'] !== (int) $plan->adaptation_plan_template_id) {
                $needsRegeneration = true;
            }
        }

        if (array_key_exists('shift', $validated) && (int) $validated['shift'] !== (int) $plan->shift) {
            $needsRegeneration = true;
        }

        if (array_key_exists('start_date', $validated) && (string) $validated['start_date'] !== $plan->start_date->toDateString()) {
            $needsRegeneration = true;
        }

        $plan->update($validated);

        if ($needsRegeneration) {
            $plan->load('template');
            $this->structureGenerator->generate($plan, true);
        }

        return response()->json($plan->fresh(['user', 'mentorUser', 'departmentHeadUser', 'template', 'days.tasks']));
    }

    public function destroy($id): JsonResponse
    {
        $plan = AdaptationPlan::findOrFail($id);
        $authUser = Auth::user();

        if (!$this->canManagePlan($plan, $authUser?->role, $authUser?->id)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $plan->delete();

        return response()->json(['message' => 'Adaptation plan deleted']);
    }

    public function updateMyInternComment(Request $request, int $dayId): JsonResponse
    {
        $validated = $request->validate([
            'intern_comment' => ['nullable', 'string', 'max:4000'],
        ]);

        $day = AdaptationPlanDay::query()
            ->where('id', $dayId)
            ->whereHas('plan', fn($query) => $query->where('user_id', Auth::id()))
            ->firstOrFail();

        $day->update([
            'intern_comment' => $validated['intern_comment'] ?? null,
        ]);

        return response()->json($day->fresh(['tasks']));
    }

    public function updateMyTaskStatus(Request $request, int $dayId, int $taskId): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:выполнено,не выполнено'],
        ]);

        $task = AdaptationPlanTask::query()
            ->where('id', $taskId)
            ->where('adaptation_plan_day_id', $dayId)
            ->whereHas('day.plan', fn($query) => $query->where('user_id', Auth::id()))
            ->firstOrFail();

        $task->update([
            'status' => $validated['status'],
        ]);

        return response()->json($task->fresh());
    }

    public function updateDay(Request $request, int $planId, int $dayId): JsonResponse
    {
        $plan = AdaptationPlan::findOrFail($planId);
        $authUser = Auth::user();

        if (!$this->canManagePlan($plan, $authUser?->role, $authUser?->id)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $validated = $request->validate([
            'date' => ['required', 'date'],
            'completion' => ['required', 'in:в процессе,выполнен,есть замечания'],
        ]);

        $day = AdaptationPlanDay::query()
            ->where('id', $dayId)
            ->where('adaptation_plan_id', $plan->id)
            ->firstOrFail();

        $day->update([
            'date' => $validated['date'],
            'completion' => $validated['completion'],
        ]);

        return response()->json($day->fresh(['tasks']));
    }

    public function updateTaskStatus(Request $request, int $planId, int $dayId, int $taskId): JsonResponse
    {
        $plan = AdaptationPlan::findOrFail($planId);
        $authUser = Auth::user();

        if (!$this->canManagePlan($plan, $authUser?->role, $authUser?->id)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $validated = $request->validate([
            'status' => ['required', 'in:выполнено,не выполнено'],
        ]);

        $task = AdaptationPlanTask::query()
            ->where('id', $taskId)
            ->where('adaptation_plan_day_id', $dayId)
            ->whereHas('day', fn($query) => $query->where('adaptation_plan_id', $plan->id))
            ->firstOrFail();

        $task->update([
            'status' => $validated['status'],
        ]);

        return response()->json($task->fresh());
    }

    private function canManagePlan(AdaptationPlan $plan, ?string $role, ?int $userId): bool
    {
        if ($role === UserRole::ADMIN->value) {
            return true;
        }

        if ($role === UserRole::MENTOR->value) {
            return $plan->mentor === $userId;
        }

        if ($role === UserRole::DEPARTMENT_HEAD->value) {
            return $plan->department_head === $userId;
        }

        return false;
    }

    private function canViewAllPlans(?string $role): bool
    {
        return in_array($role, [
            UserRole::ADMIN->value,
            UserRole::MENTOR->value,
            UserRole::DEPARTMENT_HEAD->value,
        ], true);
    }

    private function canCreatePlan(?string $role): bool
    {
        return in_array($role, [
            UserRole::ADMIN->value,
            UserRole::MENTOR->value,
            UserRole::DEPARTMENT_HEAD->value,
        ], true);
    }

    private function canViewPlan(AdaptationPlan $plan, ?string $role, ?int $userId): bool
    {
        if ($this->canManagePlan($plan, $role, $userId)) {
            return true;
        }

        return $plan->user_id === $userId;
    }

}
