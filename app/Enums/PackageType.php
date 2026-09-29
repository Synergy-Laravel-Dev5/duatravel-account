<?php

namespace App\Enums;

enum PackageType: string
{
    case SHIFTING = 'shifting';
    case FIXED_MAKKAH = 'Fixed Makkah';
    case FIXED_AZIZIA = 'Fixed Azizia';

    public static function options(): array
    {
        return [
            self::SHIFTING->value => 'Shifting',
            self::FIXED_MAKKAH->value => 'Fixed Makkah',
            self::FIXED_AZIZIA->value => 'Fixed Azizia',
        ];
    }
}
