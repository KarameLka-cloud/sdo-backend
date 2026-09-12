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
        return response()->json(
            Department::query()
                ->orderByRaw('CASE WHEN name = ? THEN 0 ELSE 1 END', ['Все отделения'])
                ->orderBy('name')
                ->get(),
        );
    }
}
