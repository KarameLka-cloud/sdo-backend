<?php

namespace App\Enums;

/** Who is accountable for an adaptation plan task. */
enum ResponsibleRole: string
{
    case DEPARTMENT_HEAD = 'Начальник отдела';
    case MENTOR = 'Наставник';
    case HR = 'Сотрудник УПиПК';
    case INTERN = 'Стажер';

    /** @return list<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
