<?php

namespace App\Enums;

/**
 * Константы прав доступа
 */
enum Permission: string
{
    // Полные права (только для администратора)
    case FULL_ACCESS = 'full_access';

        // Права наставника
    case MANAGE_EDO = 'manage_edo';
    case MANAGE_EDUCATION = 'manage_education';
    case VIEW_ALL_USERS = 'view_all_users';
    case VIEW_DEPARTMENT_USERS = 'view_department_users';

        // Права руководителя отдела
    case VIEW_DEPARTMENT_REPORT = 'view_department_report';
    case MANAGE_DEPARTMENT_STAFF = 'manage_department_staff';

    /**
     * Получить права для конкретной роли
     */
    public static function permissionsForRole(UserRole $role): array
    {
        return match ($role) {
            UserRole::ADMIN => [
                self::FULL_ACCESS,
                self::MANAGE_EDO,
                self::MANAGE_EDUCATION,
                self::VIEW_ALL_USERS,
                self::VIEW_DEPARTMENT_USERS,
                self::VIEW_DEPARTMENT_REPORT,
                self::MANAGE_DEPARTMENT_STAFF,
            ],
            UserRole::MENTOR => [
                self::MANAGE_EDO,
                self::MANAGE_EDUCATION,
                self::VIEW_DEPARTMENT_USERS,
            ],
            UserRole::DEPARTMENT_HEAD => [
                self::VIEW_DEPARTMENT_USERS,
                self::VIEW_DEPARTMENT_REPORT,
                self::MANAGE_DEPARTMENT_STAFF,
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
