<?php

namespace App\Enums;

/**
 * Константы прав доступа
 */
enum Permission: string
{
    case FULL_ACCESS = 'full_access';

    /** Просмотр списка сотрудников: нужен для назначения стажёра, наставника, руководителя отделения и начальника отдела. */
    case VIEW_USERS = 'view_users';

    /**
     * Получить права для конкретной роли
     */
    public static function permissionsForRole(UserRole $role): array
    {
        return match ($role) {
            UserRole::ADMIN => [
                self::FULL_ACCESS,
                self::VIEW_USERS,
            ],
            UserRole::MENTOR, UserRole::SUPERVISOR, UserRole::DEPARTMENT_HEAD => [
                self::VIEW_USERS,
            ],
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
