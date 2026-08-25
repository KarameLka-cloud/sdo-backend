<?php

namespace App\Http\Middleware;

use App\Enums\Permission;
use App\Services\User\RoleResolver;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RolePermission
{
    public function __construct(
        private readonly RoleResolver $roleResolver,
    ) {}

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

        $user->loadMissing('roles');

        $userRole = $this->roleResolver->resolve($user->role);

        if (!$userRole) {
            return response()->json(['message' => 'Role not assigned'], 403);
        }

        try {
            $requiredPermission = Permission::from($permission);
        } catch (\ValueError $e) {
            return response()->json([
                'message' => 'Invalid permission in route middleware',
                'required' => $permission,
            ], 500);
        }

        if (!Permission::hasPermission($userRole, $requiredPermission)) {
            return response()->json([
                'message' => 'Forbidden. You do not have required permission.',
            ], 403);
        }

        return $next($request);
    }
}
