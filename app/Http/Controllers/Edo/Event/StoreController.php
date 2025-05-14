<?php

namespace App\Http\Controllers\Edo\Event;

use App\Http\Controllers\Controller;
use App\Models\Edo\Event;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    public function __invoke(request $request)
    {
        $event = Event::create($request->only(['title', 'description', 'department', 'time']));
        return response()->json($event);
    }
}
