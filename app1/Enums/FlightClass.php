<?php

namespace App\Enums;

enum FlightClass: string
{
    case ECONOMY = 'economy';
    case BUSINESS = 'business';

    public static function options(): array
    {
        return [
            self::ECONOMY->value => 'Economy',
            self::BUSINESS->value => 'Business',
        ];
    }
}
