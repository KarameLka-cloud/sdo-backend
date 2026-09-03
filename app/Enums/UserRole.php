<?php

namespace App\Enums;

/**
 * Константы ролей пользователей в системе
 */
enum UserRole: string
{
    case ADMIN = 'ADMIN';
    case MENTOR = 'MENTOR';
    case DEPARTMENT_HEAD = 'DEPARTMENT_HEAD';

    /**
     * Получить отображаемое имя роли
     */
    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Администратор',
            self::MENTOR => 'Наставник',
            self::DEPARTMENT_HEAD => 'Руководитель отдела',
        };
    }

    /**
     * Получить все роли как массив
     */
    public static function toArray(): array
    {
        return array_map(fn ($role) => [
            'name' => $role->value,
            'label' => $role->label(),
        ], self::cases());
    }
}
