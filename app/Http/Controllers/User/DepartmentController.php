<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class DepartmentController extends Controller
{
    public function index(): \Illuminate\Http\JsonResponse
    {
        $departments = Department::all();
        return response()->json($departments);
    }

    public function show(Department $department): \Illuminate\Http\JsonResponse
    {
        return response()->json($department);
    }

    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        $department = Validator::make(
            $request->all(),
            [
                'name' => ['required', 'string', Rule::unique('departments', 'name')],
            ],
            [
                'name.required' => 'Department name is required.',
                'name.unique' => 'Name is already taken.',
            ]
        );

        if ($department->fails()) {
            return response()->json($department->errors(), 422);
        }

        $createdDepartment = Department::create($department->validated());
        return response()->json($createdDepartment, 201);
    }

    public function update(Request $request, Department $department): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make(
            $request->all(),
            [
                'name' => [
                    'required',
                    'string',
                    Rule::unique('departments', 'name')->ignore($department->id),
                ],
            ],
            [
                'name.required' => 'Department name is required.',
                'name.unique' => 'Name is already taken.',
            ]
        );

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $department->update($validator->validated());

        return response()->json($department);
    }

    public function destroy(Department $department): \Illuminate\Http\JsonResponse
    {
        $department->delete();

        return response()->json(null, 204);
    }
}
