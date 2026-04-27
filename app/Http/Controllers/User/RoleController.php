<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\RoleRequest;
use App\Enums\UserRole;
use App\Models\User\Role;
use App\Models\User\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
    public function assignRole(RoleRequest $request): JsonResponse
    {
        $validated = $request->validated();
        return $this->assignRoleByName((int) $validated['user_id'], (string) $validated['role']);
    }

    /**
     * Отозвать роль у пользователя
     */
    public function revokeRole(RoleRequest $request): JsonResponse
    {
        $validated = $request->validated();
        return $this->revokeRoleByName((int) $validated['user_id'], (string) $validated['role']);
    }

    // /**
    //  * Назначить роль admin пользователю (устаревший метод)
    //  * @deprecated Используйте assignRole с параметром role=ADMIN
    //  */
    // public function assignAdminRole(Request $request): JsonResponse
    // {
    //     $validated = $request->validate([
    //         'user_id' => 'required|integer|exists:users,id',
    //     ]);

    //     return $this->assignRoleByName((int) $validated['user_id'], UserRole::ADMIN->value);
    // }

    // /**
    //  * Отозвать роль admin у пользователя (устаревший метод)
    //  * @deprecated Используйте revokeRole с параметром role=ADMIN
    //  */
    // public function revokeAdminRole(Request $request): JsonResponse
    // {
    //     $validated = $request->validate([
    //         'user_id' => 'required|integer|exists:users,id',
    //     ]);

    //     return $this->revokeRoleByName((int) $validated['user_id'], UserRole::ADMIN->value);
    // }

    private function assignRoleByName(int $userId, string $roleName): JsonResponse
    {
        $user = User::findOrFail($userId);
        $role = Role::where('name', $roleName)->first();

        if (!$role) {
            return response()->json([
                'success' => false,
                'message' => 'Role not found in database'
            ], 404);
        }

        DB::transaction(function () use ($user, $role): void {
            // Держим инвариант: одна активная роль на пользователя.
            $user->roles()->sync([$role->id]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Role assigned successfully',
        ]);
    }

    private function revokeRoleByName(int $userId, string $roleName): JsonResponse
    {
        $user = User::findOrFail($userId);
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
}
