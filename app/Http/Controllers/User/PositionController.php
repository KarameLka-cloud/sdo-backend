<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User\Position;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PositionController extends Controller
{
    public function index(): \Illuminate\Http\JsonResponse
    {
        $positions = Position::all();
        return response()->json($positions);
    }

    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        $position = Validator::make(
            $request->all(),
            [
                'name' => ['required', 'string', 'unique'],
            ],
            [
                'name.required' => 'Position name is required.',
                'name.unique' => 'Name is already taken.',
            ]
        );

        if ($position->fails()) {
            return response()->json($position->errors(), 400);
        }

        Position::create($request->all());
        return response()->json('Position created!');
    }
}
