<?php

namespace App\Services\User;

use App\Enums\UserRole;

class RoleResolver
{
    private const ALIASES = [
        'admin' => UserRole::ADMIN,
        'full_access' => UserRole::ADMIN,
        'администратор' => UserRole::ADMIN,
        'mentor' => UserRole::MENTOR,
        'наставник' => UserRole::MENTOR,
        'supervisor' => UserRole::SUPERVISOR,
        'руководитель' => UserRole::SUPERVISOR,
        'руководитель отделения' => UserRole::SUPERVISOR,
        'руководитель отдела' => UserRole::SUPERVISOR,
        'department_head' => UserRole::DEPARTMENT_HEAD,
        'начальник отдела' => UserRole::DEPARTMENT_HEAD,
    ];

    public function resolve(?string $role): ?UserRole
    {
        if (! $role) {
            return null;
        }

        $normalizedRole = mb_strtolower(trim($role));

        if (array_key_exists($normalizedRole, self::ALIASES)) {
            return self::ALIASES[$normalizedRole];
        }

        return UserRole::tryFrom(strtoupper(trim($role)));
    }
}
