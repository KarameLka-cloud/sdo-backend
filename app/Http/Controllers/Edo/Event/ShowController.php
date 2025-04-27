<?php

namespace App\Http\Controllers\Edo\Event;

use App\Http\Controllers\Controller;
use App\Models\Edo\Event;

class ShowController extends Controller
{
    public function __invoke($id)
    {
        return Event::findOrFail($id);
    }
}
