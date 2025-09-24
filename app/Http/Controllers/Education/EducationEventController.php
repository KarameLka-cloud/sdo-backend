<?php

namespace App\Http\Controllers\Education;

use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Models\Education\EducationEvent;
use Illuminate\Http\Request;

class EducationEventController extends Controller
{
    public function index(): JsonResponse
    {
        $events = EducationEvent::orderBy('date', 'desc')->orderBy('time')->get();
        return response()->json($events);
    }

    public function store(Request $request): JsonResponse
    {
        $event = EducationEvent::create($request->all());
        return response()->json($event);
    }

    public function show($id): JsonResponse
    {
        $event = EducationEvent::findOrFail($id);
        return response()->json($event);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $event = EducationEvent::findOrFail($id);
        $event->update($request->all());
        return response()->json($event);
    }

    public function destroy($id): JsonResponse
    {
        $event = EducationEvent::findOrFail($id);
        $event->delete();
        return response()->json(['message' => 'Event deleted']);
    }
}
