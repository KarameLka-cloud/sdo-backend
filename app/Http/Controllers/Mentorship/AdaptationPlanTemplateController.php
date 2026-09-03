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
        // Deleting would detach the template from existing plans, leaving them
        // without a work schedule, so refuse while any plan still uses it.
        $plansInUse = $adaptationPlanTemplate->plans()->count();

        if ($plansInUse > 0) {
            return response()->json([
                'message' => "Шаблон используется в планах адаптации ({$plansInUse}). Сначала измените или удалите их.",
            ], 409);
        }

        $adaptationPlanTemplate->delete();

        return response()->json(['message' => 'Шаблон адаптации удалён']);
    }
}
