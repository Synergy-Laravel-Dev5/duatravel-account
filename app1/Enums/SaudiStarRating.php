<?php

namespace App\Enums;

enum SaudiStarRating: string
{
    case FIVE_STAR = '5 Star';
    case FOUR_STAR = '4 Star';
    case THREE_STAR = '3 Star';
    case TWO_STAR = '2 Star';
    case ECONOMY = 'Economy';
    case OTHER = 'Other';

    public static function options(): array
    {
        return [
            self::FIVE_STAR->value => '5 Star',
            self::FOUR_STAR->value => '4 Star',
            self::THREE_STAR->value => '3 Star',
            self::TWO_STAR->value => '2 Star',
            self::ECONOMY->value => 'Economy',
            self::OTHER->value => 'Other',
        ];
    }
}
