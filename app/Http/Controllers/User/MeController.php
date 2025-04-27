<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class MeController extends Controller
{
    public function __invoke()
    {
        return Auth::user();
    }
}
