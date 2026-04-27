<?php

namespace App\Http\Controllers\Mentorship;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdaptationPlanRequest;
use App\Models\Mentorship\AdaptationPlan;
use Illuminate\Http\JsonResponse;

class AdaptationPlanController extends Controller
{
    public function index(): JsonResponse
    {
        $plans = AdaptationPlan::with('user')->orderByDesc('id')->get();
        return response()->json($plans);
    }

    public function show($id): JsonResponse
    {
        $plan = AdaptationPlan::with('user')->findOrFail($id);
        return response()->json($plan);
    }

    public function store(AdaptationPlanRequest $request): JsonResponse
    {
        $plan = AdaptationPlan::create($request->validated());
        return response()->json($plan, 201);
    }
}
