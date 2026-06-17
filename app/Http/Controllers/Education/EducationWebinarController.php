<?php

namespace App\Http\Controllers\Education;

use App\Http\Requests\WebinarRequest;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Models\Education\EducationWebinar;

class EducationWebinarController extends Controller
{
    public function index(): JsonResponse
    {
        $webinars = EducationWebinar::orderBy('date', 'desc')->orderBy('time')->get();
        return response()->json($webinars);
    }

    public function store(WebinarRequest $request): JsonResponse
    {
        $webinar = EducationWebinar::create($request->validated());
        return response()->json($webinar);
    }

    public function show($id): JsonResponse
    {
        $webinar = EducationWebinar::findOrFail($id);
        return response()->json($webinar);
    }

    public function update(WebinarRequest $request, $id): JsonResponse
    {
        $webinar = EducationWebinar::findOrFail($id);
        $webinar->update($request->validated());
        return response()->json($webinar);
    }

    public function destroy($id): JsonResponse
    {
        $webinar = EducationWebinar::findOrFail($id);
        $webinar->delete();
        return response()->json(['message' => 'Webinar deleted']);
    }
}
