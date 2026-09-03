<?php

namespace App\Http\Controllers\User;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function me(Request $request): JsonResponse
    {
        return response()->json($request->user()?->loadMissing('roles'));
    }

    public function index(): JsonResponse
    {
        return response()->json(
            User::query()->with('roles')->orderBy('name')->get()->makeVisible('login')
        );
    }

    public function mentors(): JsonResponse
    {
        return response()->json($this->usersByRole(UserRole::MENTOR));
    }

    public function departmentHeads(): JsonResponse
    {
        return response()->json($this->usersByRole(UserRole::DEPARTMENT_HEAD));
    }

    /** Users carrying the given role, by the role's technical name. */
    private function usersByRole(UserRole $role)
    {
        return User::query()
            ->with('roles')
            ->whereHas('roles', fn ($query) => $query->where('name', $role->value))
            ->get();
    }
}
