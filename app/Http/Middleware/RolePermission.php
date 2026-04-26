<?php

namespace App\Http\Middleware;

use App\Enums\Permission;
use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RolePermission
{
    /**
     * Handle an incoming request.
     * Проверка прав доступа на основе роли пользователя
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Получаем роль пользователя
        $roleName = $user->role;
        
        if (!$roleName) {
            return response()->json(['message' => 'Role not assigned'], 403);
        }

        try {
            $userRole = UserRole::from($roleName);
        } catch (\ValueError $e) {
            return response()->json(['message' => 'Invalid role'], 403);
        }

        // Проверяем, есть ли у пользователя необходимое право
        $requiredPermission = Permission::from($permission);
        
        if (!Permission::hasPermission($userRole, $requiredPermission)) {
            return response()->json([
                'message' => 'Forbidden. You do not have required permission.',
                'required' => $permission,
                'user_role' => $roleName,
            ], 403);
        }

        return $next($request);
    }
}