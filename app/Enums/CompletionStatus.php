<?php

namespace App\Enums;

enum CompletionStatus: string
{
    case IN_PROGRESS = 'в процессе';
    case DONE = 'выполнен';
    case HAS_REMARKS = 'есть замечания';

    /** @return list<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
