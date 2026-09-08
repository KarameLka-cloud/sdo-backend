<?php

namespace App\Enums;

/**
 * Константы ролей пользователей в системе
 */
enum UserRole: string
{
    case ADMIN = 'ADMIN';
    case MENTOR = 'MENTOR';
    case SUPERVISOR = 'SUPERVISOR';
    case DEPARTMENT_HEAD = 'DEPARTMENT_HEAD';

    /** @return list<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Получить отображаемое имя роли
     */
    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Администратор',
            self::MENTOR => 'Наставник',
            self::SUPERVISOR => 'Руководитель отделения',
            self::DEPARTMENT_HEAD => 'Начальник отдела',
        };
    }

    public static function displayName(?string $name): ?string
    {
        if (! $name) {
            return null;
        }

        return self::tryFrom($name)?->label();
    }
}
