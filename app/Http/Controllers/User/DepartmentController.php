<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User\Department;
use Illuminate\Http\JsonResponse;

class DepartmentController extends Controller
{
    use ListsCatalogModels;

    public function index(): JsonResponse
    {
        return $this->listCatalog(Department::class);
    }

    public function show(Department $department): JsonResponse
    {
        return response()->json($department);
    }
}
