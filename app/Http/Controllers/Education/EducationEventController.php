<?php

namespace App\Http\Controllers\Education;

use App\Http\Controllers\Controller;
use App\Models\Education\EducationEvent;
use Illuminate\Http\Request;

class EducationEventController extends Controller
{
    public function index()
    {
        $events = EducationEvent::all();
        return response()->json($events);
    }

    public function store(Request $request)
    {
        $event = EducationEvent::create($request->only(['title', 'description', 'department', 'time', 'date']));
        return response()->json($event);
    }

    public function show($id)
    {
        $event = EducationEvent::findOrFail($id);
        return response()->json($event);
    }

    public function update(Request $request, $id)
    {
        $event = EducationEvent::findOrFail($id);
        $event->update($request->only(['title', 'description', 'department', 'time', 'date']));
        return response()->json($event);
    }

    public function destroy($id)
    {
        $event = EducationEvent::findOrFail($id);
        $event->delete();
        return response()->json(['message' => 'Event deleted']);
    }
}
