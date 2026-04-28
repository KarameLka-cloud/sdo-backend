<?php

namespace App\Http\Controllers\Mentorship;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdaptationPlanTemplateRequest;
use App\Models\Mentorship\AdaptationPlanTemplate;
use Illuminate\Http\JsonResponse;

class AdaptationPlanTemplateController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(
            AdaptationPlanTemplate::query()->orderByDesc('id')->get()
        );
    }

    public function store(AdaptationPlanTemplateRequest $request): JsonResponse
    {
        $template = AdaptationPlanTemplate::create($request->validated());
        return response()->json($template, 201);
    }

    public function show(AdaptationPlanTemplate $adaptationPlanTemplate): JsonResponse
    {
        return response()->json($adaptationPlanTemplate);
    }

    public function update(
        AdaptationPlanTemplateRequest $request,
        AdaptationPlanTemplate $adaptationPlanTemplate
    ): JsonResponse {
        $adaptationPlanTemplate->update($request->validated());
        return response()->json($adaptationPlanTemplate->fresh());
    }

    public function destroy(AdaptationPlanTemplate $adaptationPlanTemplate): JsonResponse
    {
        $adaptationPlanTemplate->delete();
        return response()->json(['message' => 'Adaptation plan template deleted']);
    }
}
