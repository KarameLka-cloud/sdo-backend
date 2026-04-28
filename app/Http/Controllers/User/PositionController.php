<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User\Position;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

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

    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        $position = Validator::make(
            $request->all(),
            [
                'name' => ['required', 'string', Rule::unique('positions', 'name')],
            ],
            [
                'name.required' => 'Position name is required.',
                'name.unique' => 'Name is already taken.',
            ]
        );

        if ($position->fails()) {
            return response()->json($position->errors(), 422);
        }

        $createdPosition = Position::create($request->all());
        return response()->json($createdPosition, 201);
    }

    public function update(Request $request, Position $position): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make(
            $request->all(),
            [
                'name' => [
                    'required',
                    'string',
                    Rule::unique('positions', 'name')->ignore($position->id),
                ],
            ],
            [
                'name.required' => 'Position name is required.',
                'name.unique' => 'Name is already taken.',
            ]
        );

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $position->update($request->all());

        return response()->json($position);
    }

    public function destroy(Position $position): \Illuminate\Http\JsonResponse
    {
        $position->delete();

        return response()->json(null, 204);
    }
}
