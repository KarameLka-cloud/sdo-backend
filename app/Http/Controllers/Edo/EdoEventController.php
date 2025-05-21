<?php

namespace App\Http\Controllers\Edo;

use App\Http\Controllers\Controller;
use App\Models\Edo\EdoEvent;
use Illuminate\Http\Request;

class EdoEventController extends Controller
{
    public function index()
    {
        $events = EdoEvent::all();
        return response()->json($events);
    }

    public function store(Request $request)
    {
        $event = EdoEvent::create($request->only(['title', 'description', 'department', 'time', 'date']));
        return response()->json($event);
    }

    public function show($id)
    {
        $event = EdoEvent::findOrFail($id);
        return response()->json($event);
    }

    public function update(Request $request, $id)
    {
        $event = EdoEvent::findOrFail($id);
        $event->update($request->only(['title', 'description', 'department', 'time', 'date']));
        return response()->json($event);
    }

    public function destroy($id)
    {
        $event = EdoEvent::findOrFail($id);
        $event->delete();
        return response()->json(['message' => 'Event deleted']);
    }
}
