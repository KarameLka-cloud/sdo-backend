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
use App\Models\User\User;
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
    ) {}

    public function all(): JsonResponse
    {
        if (!$this->canViewAllPlans(Auth::user()?->role)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $plans = AdaptationPlan::with(['user.roles', 'mentorUser.roles', 'departmentHeadUser.roles', 'template', 'days.tasks'])
            ->orderByDesc('id')
            ->get();

        return response()->json($plans);
    }

    public function my(): JsonResponse
    {
        $authUser = Auth::user();
        $plan = AdaptationPlan::with(['user.roles', 'mentorUser.roles', 'departmentHeadUser.roles', 'template', 'days.tasks'])
            ->where('user_id', $authUser?->id)
            ->orderByDesc('id')
            ->first();

        return response()->json($plan);
    }

    public function index(): JsonResponse
    {
        $authUser = Auth::user();
        $role = $this->resolveUserRole($authUser?->role);
        $userId = $authUser?->id;

        $query = AdaptationPlan::with(['user.roles', 'mentorUser.roles', 'departmentHeadUser.roles', 'template', 'days.tasks'])->orderByDesc('id');

        if ($role === UserRole::ADMIN) {
            $plans = $query->get();
            return response()->json($plans);
        }

        if ($role === UserRole::MENTOR || $role === UserRole::DEPARTMENT_HEAD) {
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
        $plan = AdaptationPlan::with(['user.roles', 'mentorUser.roles', 'departmentHeadUser.roles', 'template', 'days.tasks'])->findOrFail($id);
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
        if ($roleErrorResponse = $this->validateAssigneeRoles($validated)) {
            return $roleErrorResponse;
        }
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

        return response()->json($plan->fresh(['user.roles', 'mentorUser.roles', 'departmentHeadUser.roles', 'template', 'days.tasks']), 201);
    }

    public function update(AdaptationPlanUpdateRequest $request, $id): JsonResponse
    {
        $plan = AdaptationPlan::findOrFail($id);
        $authUser = Auth::user();

        if (!$this->canManagePlan($plan, $authUser?->role, $authUser?->id)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $validated = $request->validated();
        if ($roleErrorResponse = $this->validateAssigneeRoles($validated, $plan)) {
            return $roleErrorResponse;
        }

        if (array_key_exists('adaptation_plan_template_id', $validated)) {
            $template = AdaptationPlanTemplate::findOrFail($validated['adaptation_plan_template_id']);
            $targetShift = $validated['shift'] ?? $plan->shift;

            if (!in_array($targetShift, $template->shifts ?? [], true)) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => [
                        'shift' => ['Выбранная смена недоступна для указанного шаблона.'],
                    ],
                ], 422);
            }
        }

        DB::transaction(function () use (&$plan, $validated) {
            $needsRegeneration = false;
            $updatePayload = $validated;

            if (array_key_exists('adaptation_plan_template_id', $updatePayload)) {
                $template = AdaptationPlanTemplate::findOrFail($updatePayload['adaptation_plan_template_id']);
                $updatePayload['work_schedule'] = $template->work_schedule;

                if ((int) $updatePayload['adaptation_plan_template_id'] !== (int) $plan->adaptation_plan_template_id) {
                    $needsRegeneration = true;
                }
            }

            if (array_key_exists('shift', $updatePayload) && (int) $updatePayload['shift'] !== (int) $plan->shift) {
                $needsRegeneration = true;
            }

            if (array_key_exists('start_date', $updatePayload) && (string) $updatePayload['start_date'] !== $plan->start_date->toDateString()) {
                $needsRegeneration = true;
            }

            $plan->update($updatePayload);

            if ($needsRegeneration) {
                $plan->load('template');
                $this->structureGenerator->generate($plan, true);
            }
        });

        return response()->json($plan->fresh(['user.roles', 'mentorUser.roles', 'departmentHeadUser.roles', 'template', 'days.tasks']));
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
            'date_from' => ['required', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'completion' => ['required', 'in:в процессе,выполнен,есть замечания'],
            'employee_comment' => ['nullable', 'string', 'max:4000'],
            'intern_comment' => ['nullable', 'string', 'max:4000'],
            'mentor_comment' => ['nullable', 'string', 'max:4000'],
            'department_head_comment' => ['nullable', 'string', 'max:4000'],
        ]);

        $day = AdaptationPlanDay::query()
            ->where('id', $dayId)
            ->where('adaptation_plan_id', $plan->id)
            ->firstOrFail();

        $day->update([
            'date_from' => $validated['date_from'],
            'date_to' => $validated['date_to'] ?? null,
            'completion' => $validated['completion'],
            'employee_comment' => $validated['employee_comment'] ?? null,
            'intern_comment' => $validated['intern_comment'] ?? null,
            'mentor_comment' => $validated['mentor_comment'] ?? null,
            'department_head_comment' => $validated['department_head_comment'] ?? null,
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
        if ($userId === null) {
            return false;
        }

        $resolvedRole = $this->resolveUserRole($role);

        if ($resolvedRole === UserRole::ADMIN) {
            return true;
        }

        if ((int) $plan->mentor === (int) $userId) {
            return true;
        }

        if ((int) $plan->department_head === (int) $userId) {
            return true;
        }

        return false;
    }

    private function canViewAllPlans(?string $role): bool
    {
        return in_array($this->resolveUserRole($role), [
            UserRole::ADMIN,
        ], true);
    }

    private function canCreatePlan(?string $role): bool
    {
        return in_array($this->resolveUserRole($role), [
            UserRole::ADMIN,
            UserRole::MENTOR,
            UserRole::DEPARTMENT_HEAD,
        ], true);
    }

    private function canViewPlan(AdaptationPlan $plan, ?string $role, ?int $userId): bool
    {
        if ($this->canManagePlan($plan, $role, $userId)) {
            return true;
        }

        if ($userId === null) {
            return false;
        }

        return (int) $plan->user_id === (int) $userId;
    }

    private function resolveUserRole(?string $role): ?UserRole
    {
        if (!$role) {
            return null;
        }

        $normalizedRole = mb_strtolower(trim($role));
        $aliases = [
            'admin' => UserRole::ADMIN,
            'full_access' => UserRole::ADMIN,
            'администратор' => UserRole::ADMIN,
            'mentor' => UserRole::MENTOR,
            'наставник' => UserRole::MENTOR,
            'department_head' => UserRole::DEPARTMENT_HEAD,
            'руководитель отдела' => UserRole::DEPARTMENT_HEAD,
        ];

        if (array_key_exists($normalizedRole, $aliases)) {
            return $aliases[$normalizedRole];
        }

        return UserRole::tryFrom(strtoupper(trim($role)));
    }

    private function validateAssigneeRoles(array $payload, ?AdaptationPlan $currentPlan = null): ?JsonResponse
    {
        $mentorChanged = array_key_exists('mentor', $payload)
            && ((int) $payload['mentor'] !== (int) $currentPlan?->mentor);
        if ($mentorChanged && !$this->userHasRole((int) $payload['mentor'], UserRole::MENTOR)) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => [
                    'mentor' => ['Выбранный пользователь не является наставником.'],
                ],
            ], 422);
        }

        $departmentHeadChanged = array_key_exists('department_head', $payload)
            && ((int) $payload['department_head'] !== (int) $currentPlan?->department_head);
        if ($departmentHeadChanged && !$this->userHasRole((int) $payload['department_head'], UserRole::DEPARTMENT_HEAD)) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => [
                    'department_head' => ['Выбранный пользователь не является руководителем отдела.'],
                ],
            ], 422);
        }

        return null;
    }

    private function userHasRole(int $userId, UserRole $requiredRole): bool
    {
        $user = User::query()->with('roles')->find($userId);
        if (!$user) {
            return false;
        }

        $resolvedRole = $this->resolveUserRole($user->role ?? $user->role_name ?? null);
        return $resolvedRole === $requiredRole;
    }
}
