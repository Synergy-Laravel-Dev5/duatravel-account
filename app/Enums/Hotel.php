<?php

namespace App\Enums;

enum Hotel: string
{
    case PULLMAN_ZAMZAM = 'Pullman ZamZam Makkah';
    case SWISSOTEL = 'Swissôtel Makkah';
    case FAIRMONT = 'Makkah Clock Royal Tower, A Fairmont Hotel';
    case DAR_AL_TAQWA = 'Dar Al Taqwa Hotel Madinah';
    case OBEROI = 'The Oberoi Madinah';
    case ANJUM = 'Anjum Hotel Makkah';
    case SHAZA = 'Shaza Regency Plaza Madinah';
    case OTHER = 'Other';

    public static function options(): array
    {
        return [
            self::PULLMAN_ZAMZAM->value => 'Pullman ZamZam Makkah',
            self::SWISSOTEL->value => 'Swissôtel Makkah',
            self::FAIRMONT->value => 'Makkah Clock Royal Tower, A Fairmont Hotel',
            self::DAR_AL_TAQWA->value => 'Dar Al Taqwa Hotel Madinah',
            self::OBEROI->value => 'The Oberoi Madinah',
            self::ANJUM->value => 'Anjum Hotel Makkah',
            self::SHAZA->value => 'Shaza Regency Plaza Madinah',
            self::OTHER->value => 'Other',
        ];
    }
}
