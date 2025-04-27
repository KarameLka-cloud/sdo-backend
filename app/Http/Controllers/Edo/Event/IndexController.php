<?php

namespace App\Http\Controllers\Edo\Event;

use App\Http\Controllers\Controller;
use App\Models\Edo\Event;

class IndexController extends Controller
{
    public function __invoke()
    {
        return Event::all();
    }
}
