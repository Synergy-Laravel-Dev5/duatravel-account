<?php

namespace App\Enums;

enum Place: string
{
    case MAKKAH = 'Makkah';
    case MADINAH = 'Madinah';
    case AZIZIA = 'Azizia';
    case MINA_ARAFAT = 'Mina/Arafat';

    public static function options(): array
    {
        return [
            self::MAKKAH->value => 'Makkah',
            self::MADINAH->value => 'Madinah',
            self::AZIZIA->value => 'Azizia',
            self::MINA_ARAFAT->value => 'Mina/Arafat',
        ];
    }
}
