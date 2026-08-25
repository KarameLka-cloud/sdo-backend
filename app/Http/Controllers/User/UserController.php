<?php

namespace App\Http\Controllers\User;

use App\Enums\UserRole;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Models\User\User;

class UserController extends Controller
{
    public function index(): JsonResponse
    {
        $users = User::query()->with('roles')->get();
        return response()->json($users);
    }

    public function mentors(): JsonResponse
    {
        $mentors = $this->usersByRole(UserRole::MENTOR);

        return response()->json($mentors);
    }

    public function departmentHeads(): JsonResponse
    {
        $departmentHeads = $this->usersByRole(UserRole::DEPARTMENT_HEAD);

        return response()->json($departmentHeads);
    }

    /**
     * Возвращает пользователей по техническому имени роли (enum value).
     */
    private function usersByRole(UserRole $role)
    {
        $roleName = $role->value;

        return User::query()
            ->with('roles')
            ->whereHas('roles', function ($query) use ($roleName): void {
                $query->where('name', $roleName);
            })
            ->get();
    }
}
