<?php

namespace App\Enums;

enum YesNo: string
{
    case YES = 'yes';
    case NO = 'no';

    public static function options(): array
    {
        return [
            self::YES->value => 'Yes',
            self::NO->value => 'No',
        ];
    }
}
