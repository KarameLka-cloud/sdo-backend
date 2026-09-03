<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User\Position;
use Illuminate\Http\JsonResponse;

class PositionController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Position::query()->orderBy('name')->get());
    }

    public function show(Position $position): JsonResponse
    {
        return response()->json($position);
    }
}
