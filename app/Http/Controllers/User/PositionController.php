<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User\Position;

class PositionController extends Controller
{
    public function index(): \Illuminate\Http\JsonResponse
    {
        $positions = Position::all();
        return response()->json($positions);
    }

    public function show(Position $position): \Illuminate\Http\JsonResponse
    {
        return response()->json($position);
    }
}
