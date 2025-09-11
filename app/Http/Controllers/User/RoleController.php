<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User\Role;
use App\Models\User\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    /**
     * Назначить роль admin пользователю
     */
    public function assignAdminRole(Request $request): JsonResponse
    {

        $user = User::findOrFail($request->id);
        $adminRole = Role::where('name', 'ADMIN')->first();

        if ($user->roles()->where('name', $adminRole->name)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'User already has admin role'
            ], 400);
        }

        $user->roles()->attach($adminRole->id);

        return response()->json([
            'success' => true,
            'message' => 'Admin role assigned successfully',
        ]);
    }

    /**
     * Отозвать роль admin у пользователя
     */
    public function revokeAdminRole(Request $request): JsonResponse
    {
        $user = User::findOrFail($request->id);
        $adminRole = Role::where('name', 'ADMIN')->first();

        if (!$user->roles()->where('name', $adminRole->name)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'User does not have admin role'
            ], 400);
        }

        $user->roles()->detach($adminRole->id);

        return response()->json([
            'success' => true,
            'message' => 'Admin role revoked successfully',
        ]);
    }
}
