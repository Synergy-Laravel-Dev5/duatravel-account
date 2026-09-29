<?php

namespace App\Enums;

enum AccommodationCategory: string
{
    case ONE_STAR = '1 Star';
    case TWO_STAR = '2 Star';
    case THREE_STAR = '3 Star';
    case FOUR_STAR = '4 Star';
    case FIVE_STAR = '5 Star';

    public static function options(): array
    {
        return [
            self::ONE_STAR->value => '1 Star',
            self::TWO_STAR->value => '2 Star',
            self::THREE_STAR->value => '3 Star',
            self::FOUR_STAR->value => '4 Star',
            self::FIVE_STAR->value => '5 Star',
        ];
    }
}
