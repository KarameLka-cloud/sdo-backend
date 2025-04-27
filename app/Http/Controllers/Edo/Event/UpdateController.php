<?php

namespace App\Http\Controllers\Edo\Event;

use App\Http\Controllers\Controller;
use App\Models\Edo\Event;
use Illuminate\Http\Request;

class UpdateController extends Controller
{
    public function __invoke(request $request, $id)
    {
        $event = Event::findOrFail($id);
        $event->update($request->only(['title', 'description', 'department', 'time']));
        return response()->json($event);
    }
}
