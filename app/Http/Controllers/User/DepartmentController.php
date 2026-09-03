<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User\Department;
use Illuminate\Http\JsonResponse;

class DepartmentController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Department::query()->orderBy('name')->get());
    }

    public function show(Department $department): JsonResponse
    {
        return response()->json($department);
    }
}
