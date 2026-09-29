<?php

namespace App\Enums;

enum FlightType: string
{
    case DIRECT = 'direct';
    case INDIRECT = 'indirect';

    public static function options(): array
    {
        return [
            self::DIRECT->value => 'Direct',
            self::INDIRECT->value => 'Indirect',
        ];
    }
}
