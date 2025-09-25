<?php

namespace App\Http\Controllers\Edo;

use App\Http\Requests\EventRequest;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Models\Edo\EdoEvent;

class EdoEventController extends Controller
{
    public function index(): JsonResponse
    {
        $events = EdoEvent::orderBy('date', 'desc')->orderBy('time')->get();
        return response()->json($events);
    }

    public function store(EventRequest $request): JsonResponse
    {
        $event = EdoEvent::create($request->validated());
        return response()->json($event);
    }

    public function show($id): JsonResponse
    {
        $event = EdoEvent::findOrFail($id);
        return response()->json($event);
    }

    public function update(EventRequest $request, $id): JsonResponse
    {
        $event = EdoEvent::findOrFail($id);
        $event->update($request->validated());
        return response()->json($event);
    }

    public function destroy($id): JsonResponse
    {
        $event = EdoEvent::findOrFail($id);
        $event->delete();
        return response()->json(['message' => 'Event deleted']);
    }
}
