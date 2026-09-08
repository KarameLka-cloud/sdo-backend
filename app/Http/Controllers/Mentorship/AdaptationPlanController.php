<?php

namespace App\Http\Controllers\Mentorship;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdaptationPlanDayUpdateRequest;
use App\Http\Requests\AdaptationPlanInternCommentRequest;
use App\Http\Requests\AdaptationPlanRequest;
use App\Http\Requests\AdaptationPlanTaskStatusRequest;
use App\Http\Requests\AdaptationPlanUpdateRequest;
use App\Models\Mentorship\AdaptationPlan;
use App\Models\Mentorship\AdaptationPlanTask;
use App\Services\Mentorship\AdaptationPlanService;
use App\Services\User\RoleResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdaptationPlanController extends Controller
{
    public function __construct(
        private readonly AdaptationPlanService $adaptationPlanService,
        private readonly RoleResolver $roleResolver,
    ) {}

    public function my(Request $request): JsonResponse
    {
        $plan = AdaptationPlan::with(AdaptationPlanService::PLAN_RELATIONS)
            ->where('user_id', $request->user()->id)
            ->orderByDesc('id')
            ->first();

        return response()->json($plan);
    }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user()->loadMissing('roles');

        $plans = AdaptationPlan::with(AdaptationPlanService::PLAN_LIST_RELATIONS)
            ->visibleTo($user, $this->roleResolver->resolve($user->role))
            ->orderByDesc('id')
            ->get();

        return response()->json($plans);
    }

    public function show(AdaptationPlan $adaptationPlan): JsonResponse
    {
        $this->authorize('view', $adaptationPlan);

        return response()->json(
            $adaptationPlan->load(AdaptationPlanService::PLAN_RELATIONS)
        );
    }

    public function store(AdaptationPlanRequest $request): JsonResponse
    {
        $this->authorize('create', AdaptationPlan::class);

        return response()->json(
            $this->adaptationPlanService->create($request->validated()),
            201
        );
    }

    public function update(
        AdaptationPlanUpdateRequest $request,
        AdaptationPlan $adaptationPlan,
    ): JsonResponse {
        $this->authorize('updateMeta', $adaptationPlan);

        return response()->json(
            $this->adaptationPlanService->update($adaptationPlan, $request->validated())
        );
    }

    public function destroy(AdaptationPlan $adaptationPlan): JsonResponse
    {
        $this->authorize('delete', $adaptationPlan);
        $this->adaptationPlanService->delete($adaptationPlan);

        return response()->json(['message' => 'План адаптации удалён']);
    }

    public function updateMyInternComment(
        AdaptationPlanInternCommentRequest $request,
        int $dayId,
    ): JsonResponse {
        $day = $this->adaptationPlanService->findOwnedDay($dayId, (int) $request->user()->id);

        return response()->json($this->adaptationPlanService->updateInternComment(
            $day,
            $request->validated()['intern_comment'] ?? null,
        ));
    }

    public function updateMyTaskStatus(
        AdaptationPlanTaskStatusRequest $request,
        int $dayId,
        int $taskId,
    ): JsonResponse {
        return $this->respondWithTaskStatus(
            $this->adaptationPlanService->findOwnedTask($dayId, $taskId, (int) $request->user()->id),
            $request->validated()['status'],
        );
    }

    public function updateDay(
        AdaptationPlanDayUpdateRequest $request,
        AdaptationPlan $adaptationPlan,
        int $dayId,
    ): JsonResponse {
        $this->authorize('manage', $adaptationPlan);
        $day = $this->adaptationPlanService->findManagedDay($adaptationPlan, $dayId);

        return response()->json(
            $this->adaptationPlanService->updateDay(
                $day,
                $request->validated(),
                $request->user(),
            )
        );
    }

    public function updateTaskStatus(
        AdaptationPlanTaskStatusRequest $request,
        AdaptationPlan $adaptationPlan,
        int $dayId,
        int $taskId,
    ): JsonResponse {
        $this->authorize('manage', $adaptationPlan);

        return $this->respondWithTaskStatus(
            $this->adaptationPlanService->findManagedTask($adaptationPlan, $dayId, $taskId),
            $request->validated()['status'],
        );
    }

    /** Shared tail of the intern-owned and manager-driven status updates. */
    private function respondWithTaskStatus(AdaptationPlanTask $task, string $status): JsonResponse
    {
        return response()->json(
            $this->adaptationPlanService->updateTaskStatus($task, $status)
        );
    }
}
