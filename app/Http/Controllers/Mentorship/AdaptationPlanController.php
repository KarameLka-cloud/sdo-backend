<?php

namespace App\Http\Controllers\Mentorship;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\AdaptationPlanDayUpdateRequest;
use App\Http\Requests\AdaptationPlanInternCommentRequest;
use App\Http\Requests\AdaptationPlanRequest;
use App\Http\Requests\AdaptationPlanTaskStatusRequest;
use App\Http\Requests\AdaptationPlanUpdateRequest;
use App\Models\Mentorship\AdaptationPlan;
use App\Services\Mentorship\AdaptationPlanService;
use App\Services\User\RoleResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class AdaptationPlanController extends Controller
{
    public function __construct(
        private readonly AdaptationPlanService $adaptationPlanService,
        private readonly RoleResolver $roleResolver,
    ) {}

    public function my(): JsonResponse
    {
        $authUser = Auth::user();
        $plan = AdaptationPlan::with(AdaptationPlanService::PLAN_RELATIONS)
            ->where('user_id', $authUser?->id)
            ->orderByDesc('id')
            ->first();

        return response()->json($plan);
    }

    public function index(): JsonResponse
    {
        $authUser = Auth::user();
        $authUser?->loadMissing('roles');
        $role = $this->roleResolver->resolve($authUser?->role);
        $userId = $authUser?->id;

        $query = AdaptationPlan::with(AdaptationPlanService::PLAN_LIST_RELATIONS)->orderByDesc('id');

        if ($role === UserRole::ADMIN) {
            return response()->json($query->get());
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

        return response()->json($query->where('user_id', $userId)->get());
    }

    public function show($id): JsonResponse
    {
        $plan = AdaptationPlan::with(AdaptationPlanService::PLAN_RELATIONS)->findOrFail($id);
        $this->authorize('view', $plan);

        return response()->json($plan);
    }

    public function store(AdaptationPlanRequest $request): JsonResponse
    {
        $this->authorize('create', AdaptationPlan::class);
        $plan = $this->adaptationPlanService->create($request->validated());

        return response()->json($plan, 201);
    }

    public function update(AdaptationPlanUpdateRequest $request, $id): JsonResponse
    {
        $plan = AdaptationPlan::findOrFail($id);
        $this->authorize('update', $plan);
        $plan = $this->adaptationPlanService->update($plan, $request->validated());

        return response()->json($plan);
    }

    public function destroy($id): JsonResponse
    {
        $plan = AdaptationPlan::findOrFail($id);
        $this->authorize('delete', $plan);
        $plan->delete();

        return response()->json(['message' => 'Adaptation plan deleted']);
    }

    public function updateMyInternComment(AdaptationPlanInternCommentRequest $request, int $dayId): JsonResponse
    {
        $day = $this->adaptationPlanService->findOwnedDay($dayId, (int) Auth::id());
        $day = $this->adaptationPlanService->updateInternComment(
            $day,
            $request->validated()['intern_comment'] ?? null,
        );

        return response()->json($day);
    }

    public function updateMyTaskStatus(AdaptationPlanTaskStatusRequest $request, int $dayId, int $taskId): JsonResponse
    {
        $task = $this->adaptationPlanService->findOwnedTask($dayId, $taskId, (int) Auth::id());
        $task = $this->adaptationPlanService->updateTaskStatus(
            $task,
            $request->validated()['status'],
        );

        return response()->json($task);
    }

    public function updateDay(AdaptationPlanDayUpdateRequest $request, int $planId, int $dayId): JsonResponse
    {
        $plan = AdaptationPlan::findOrFail($planId);
        $this->authorize('manage', $plan);
        $day = $this->adaptationPlanService->findManagedDay($plan, $dayId);
        $day = $this->adaptationPlanService->updateDay($day, $request->validated());

        return response()->json($day);
    }

    public function updateTaskStatus(
        AdaptationPlanTaskStatusRequest $request,
        int $planId,
        int $dayId,
        int $taskId,
    ): JsonResponse {
        $plan = AdaptationPlan::findOrFail($planId);
        $this->authorize('manage', $plan);
        $task = $this->adaptationPlanService->findManagedTask($plan, $dayId, $taskId);
        $task = $this->adaptationPlanService->updateTaskStatus(
            $task,
            $request->validated()['status'],
        );

        return response()->json($task);
    }
}
