<?php

namespace App\Http\Controllers\Mentorship;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\AdaptationPlanRequest;
use App\Models\Mentorship\AdaptationPlan;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class AdaptationPlanController extends Controller
{
    public function index(): JsonResponse
    {
        $authUser = Auth::user();
        $role = $authUser?->role;

        $query = AdaptationPlan::with(['user', 'mentorUser', 'departmentHeadUser'])->orderByDesc('id');

        if ($role === UserRole::ADMIN->value) {
            $plans = $query->get();
            return response()->json($plans);
        }

        if ($role === UserRole::MENTOR->value) {
            $plans = $query->where('mentor', $authUser?->id)->get();
            return response()->json($plans);
        }

        if ($role === UserRole::DEPARTMENT_HEAD->value) {
            $plans = $query->where('department_head', $authUser?->id)->get();
            return response()->json($plans);
        }

        $plans = $query->where('user_id', $authUser?->id)->get();
        return response()->json($plans);
    }

    public function show($id): JsonResponse
    {
        $plan = AdaptationPlan::with(['user', 'mentorUser', 'departmentHeadUser'])->findOrFail($id);
        return response()->json($plan);
    }

    public function store(AdaptationPlanRequest $request): JsonResponse
    {
        $plan = AdaptationPlan::create($request->validated());
        return response()->json($plan->fresh(['user', 'mentorUser', 'departmentHeadUser']), 201);
    }

    public function update($id): JsonResponse
    {
        $plan = AdaptationPlan::findOrFail($id);
        $authUser = Auth::user();

        if (!$this->canManagePlan($plan, $authUser?->role, $authUser?->id)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $validated = request()->validate([
            'mentor' => ['required', 'integer', 'exists:users,id'],
            'department_head' => ['required', 'integer', 'exists:users,id'],
        ]);

        $plan->update($validated);

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
}
