<?php

namespace App\Enums;

/**
 * Константы прав доступа
 */
enum Permission: string
{
    case FULL_ACCESS = 'full_access';

    /**
     * Получить права для конкретной роли
     */
    public static function permissionsForRole(UserRole $role): array
    {
        return match ($role) {
            UserRole::ADMIN => [
                self::FULL_ACCESS,
            ],
            UserRole::MENTOR, UserRole::DEPARTMENT_HEAD => [],
        };
    }

    /**
     * Проверить, имеет ли роль определенное право
     */
    public static function hasPermission(UserRole $role, Permission $permission): bool
    {
        return in_array($permission, self::permissionsForRole($role), true);
    }
}
