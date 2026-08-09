<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User\Department;

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
}
