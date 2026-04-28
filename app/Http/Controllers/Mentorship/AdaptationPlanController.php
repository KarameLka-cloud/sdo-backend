<?php

namespace App\Http\Controllers\Mentorship;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\AdaptationPlanRequest;
use App\Http\Requests\AdaptationPlanUpdateRequest;
use App\Models\Mentorship\AdaptationPlan;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;

class AdaptationPlanController extends Controller
{
    public function all(): JsonResponse
    {
        if (!$this->canViewAllPlans(Auth::user()?->role)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $plans = AdaptationPlan::with(['user', 'mentorUser', 'departmentHeadUser'])
            ->orderByDesc('id')
            ->get();

        return response()->json($plans);
    }

    public function my(): JsonResponse
    {
        $authUser = Auth::user();
        $plan = AdaptationPlan::with(['user', 'mentorUser', 'departmentHeadUser'])
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

        $query = AdaptationPlan::with(['user', 'mentorUser', 'departmentHeadUser'])->orderByDesc('id');

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
        $plan = AdaptationPlan::with(['user', 'mentorUser', 'departmentHeadUser'])->findOrFail($id);
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

        try {
            $plan = AdaptationPlan::create($request->validated());
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

        return response()->json($plan->fresh(['user', 'mentorUser', 'departmentHeadUser']), 201);
    }

    public function update(AdaptationPlanUpdateRequest $request, $id): JsonResponse
    {
        $plan = AdaptationPlan::findOrFail($id);
        $authUser = Auth::user();

        if (!$this->canManagePlan($plan, $authUser?->role, $authUser?->id)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $plan->update($request->validated());

        return response()->json($plan->fresh(['user', 'mentorUser', 'departmentHeadUser']));
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
