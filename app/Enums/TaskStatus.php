<?php

namespace App\Enums;

enum TaskStatus: string
{
    case DONE = 'выполнено';
    case NOT_DONE = 'не выполнено';

    /** @return list<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
