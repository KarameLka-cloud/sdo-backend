<?php

namespace App\Http\Controllers\Edo\Event;

use App\Http\Controllers\Controller;
use App\Models\Edo\Event;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index()
    {
        $events = Event::all();
        return response()->json($events);
    }

    public function store(Request $request)
    {
        $event = Event::create($request->only(['title', 'description', 'department', 'time']));
        return response()->json($event);
    }

    public function show($id)
    {
        $event = Event::findOrFail($id);
        return response()->json($event);
    }

    public function update(Request $request, $id)
    {
        $event = Event::findOrFail($id);
        $event->update($request->only(['title', 'description', 'department', 'time']));
        return response()->json($event);
    }

    public function destroy($id)
    {
        return response()->json(["destroy" => $id]);
    }
}
