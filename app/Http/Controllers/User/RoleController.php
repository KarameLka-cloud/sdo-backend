<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Enums\UserRole;
use App\Models\User\Role;
use App\Models\User\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    /**
     * Получить список всех доступных ролей
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => UserRole::toArray(),
        ]);
    }

    /**
     * Назначить роль пользователю (заменяет все существующие роли)
     */
    public function assignRole(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'role' => 'required|string',
        ]);

        $user = User::findOrFail($request->user_id);
        $roleName = strtoupper($request->role);

        // Проверяем, что роль существует в enum
        if (!in_array($roleName, array_column(UserRole::cases(), 'value'))) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid role specified'
            ], 400);
        }

        $role = Role::where('name', $roleName)->first();

        if (!$role) {
            return response()->json([
                'success' => false,
                'message' => 'Role not found in database'
            ], 404);
        }

        // Удаляем все существующие роли пользователя
        $user->roles()->detach();

        // Назначаем новую роль
        $user->roles()->attach($role->id);

        return response()->json([
            'success' => true,
            'message' => 'Role assigned successfully',
        ]);
    }

    /**
     * Отозвать роль у пользователя
     */
    public function revokeRole(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'role' => 'required|string',
        ]);

        $user = User::findOrFail($request->user_id);
        $roleName = strtoupper($request->role);

        // Проверяем, что роль существует в enum
        if (!in_array($roleName, array_column(UserRole::cases(), 'value'))) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid role specified'
            ], 400);
        }

        $role = Role::where('name', $roleName)->first();

        if (!$role) {
            return response()->json([
                'success' => false,
                'message' => 'Role not found in database'
            ], 404);
        }

        if (!$user->roles()->where('name', $role->name)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'User does not have this role'
            ], 400);
        }

        $user->roles()->detach($role->id);

        return response()->json([
            'success' => true,
            'message' => 'Role revoked successfully',
        ]);
    }

    /**
     * Назначить роль admin пользователю (устаревший метод)
     * @deprecated Используйте assignRole с параметром role=ADMIN
     */
    public function assignAdminRole(Request $request): JsonResponse
    {
        $request->merge(['role' => 'ADMIN']);
        return $this->assignRole($request);
    }

    /**
     * Отозвать роль admin у пользователя (устаревший метод)
     * @deprecated Используйте revokeRole с параметром role=ADMIN
     */
    public function revokeAdminRole(Request $request): JsonResponse
    {
        $request->merge(['role' => 'ADMIN']);
        return $this->revokeRole($request);
    }
}
