<?php

namespace App\Enums;

enum AccommodationType: string
{
    case HOTEL = 'Hotel';
    case MAKTAB = 'Maktab';
    case BUILDING_FLAT = 'Building/Flat';

    public static function options(): array
    {
        return [
            self::HOTEL->value => 'Hotel',
            self::MAKTAB->value => 'Maktab',
            self::BUILDING_FLAT->value => 'Building/Flat',
        ];
    }
}
