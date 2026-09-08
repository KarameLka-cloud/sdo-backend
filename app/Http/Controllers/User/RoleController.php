<?php

namespace App\Http\Controllers\User;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\RoleRequest;
use App\Models\User\Role;
use App\Models\User\User;
use Illuminate\Http\JsonResponse;

class RoleController extends Controller
{
    /** All roles that can be assigned. */
    public function index(): JsonResponse
    {
        $order = array_flip(UserRole::values());

        return response()->json(
            Role::query()
                ->get(['name', 'display_name'])
                ->sortBy(fn (Role $role) => $order[$role->name] ?? 99)
                ->values()
                ->map(fn (Role $role) => [
                    'name' => $role->name,
                    'label' => UserRole::displayName($role->name) ?? $role->display_name,
                ])
        );
    }

    /** Assigns a role, replacing any the user already has. */
    public function assignRole(RoleRequest $request): JsonResponse
    {
        [$user, $role] = $this->resolveUserAndRole($request);

        // One active role per user is an application-level invariant.
        $user->roles()->sync([$role->id]);

        return response()->json(['message' => 'Role assigned successfully']);
    }

    public function revokeRole(RoleRequest $request): JsonResponse
    {
        [$user, $role] = $this->resolveUserAndRole($request);
        $user->loadMissing('roles');

        if (! $user->roles->contains('id', $role->id)) {
            return response()->json(['message' => 'User does not have this role'], 400);
        }

        $user->roles()->detach($role->id);

        return response()->json(['message' => 'Role revoked successfully']);
    }

    /**
     * @return array{0: User, 1: Role}
     */
    private function resolveUserAndRole(RoleRequest $request): array
    {
        $validated = $request->validated();

        return [
            User::findOrFail((int) $validated['user_id']),
            Role::where('name', (string) $validated['role'])->firstOrFail(),
        ];
    }
}
