<?php

namespace App\Http\Controllers\Education;

use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Models\Education\EducationWebinar;
use Illuminate\Http\Request;

class EducationWebinarController extends Controller
{
    public function index(): JsonResponse
    {
        $webinars = EducationWebinar::orderBy('date', 'desc')->orderBy('time_start')->get();
        return response()->json($webinars);
    }

    public function store(Request $request): JsonResponse
    {
        $webinar = EducationWebinar::create($request->only(['title', 'time_start', 'time_end', 'date']));
        return response()->json($webinar);
    }

    public function show($id): JsonResponse
    {
        $webinar = EducationWebinar::findOrFail($id);
        return response()->json($webinar);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $webinar = EducationWebinar::findOrFail($id);
        $webinar->update($request->only(['title', 'time_start', 'time_end', 'date']));
        return response()->json($webinar);
    }

    public function destroy($id): JsonResponse
    {
        $webinar = EducationWebinar::findOrFail($id);
        $webinar->delete();
        return response()->json(['message' => 'Webinar deleted']);
    }
}
