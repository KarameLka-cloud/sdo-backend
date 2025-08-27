<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Models\User\User;

class UserController extends Controller
{
    public function index(): JsonResponse
    {
        $users = User::all();
        return response()->json($users);
    }
}
