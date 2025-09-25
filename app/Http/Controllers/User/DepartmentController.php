<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DepartmentController extends Controller
{
    public function index(): \Illuminate\Http\JsonResponse
    {
        $departments = Department::all();
        return response()->json($departments);
    }

    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        $department = Validator::make($request->all(), [
            'name' => ['required', 'string', 'unique'],
        ],
            [
                'name.required' => 'Department name is required.',
                'name.unique' => 'Name is already taken.',
            ]);

        if ($department->fails()) {
            return response()->json($department->errors(), 400);
        }

        Department::create($request->all());
        return response()->json('Department created!');
    }
}
