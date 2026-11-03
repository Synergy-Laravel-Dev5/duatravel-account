<?php

namespace App\Enums;

enum Place: string
{
    // KSA / Pilgrimage
    case MAKKAH = 'Makkah';
    case MADINAH = 'Madinah';
    case AZIZIA = 'Azizia';
    case JEDDAH = 'Jeddah';
    case MINA_ARAFAT = 'Mina/Arafat';

    // Pakistan Domestic Cities
    case KARACHI = 'Karachi';
    case LAHORE = 'Lahore';
    case ISLAMABAD = 'Islamabad';
    case RAWALPINDI = 'Rawalpindi';
    case PESHAWAR = 'Peshawar';
    case MULTAN = 'Multan';
    case FAISALABAD = 'Faisalabad';
    case QUETTA = 'Quetta';
    case SIALKOT = 'Sialkot';

    // International Cities
    case DUBAI = 'Dubai';
    case ABU_DHABI = 'Abu Dhabi';
    case SHARJAH = 'Sharjah';
    case ISTANBUL = 'Istanbul';
    case BAKU = 'Baku';
    case LONDON = 'London';
    case BANGKOK = 'Bangkok';
    case KUALA_LUMPUR = 'Kuala Lumpur';
    case DOHA = 'Doha';

    public static function options(): array
    {
        return [
            // Pilgrimage
            self::MAKKAH->value => 'Makkah',
            self::MADINAH->value => 'Madinah',
            self::AZIZIA->value => 'Azizia',
            self::JEDDAH->value => 'Jeddah',
            self::MINA_ARAFAT->value => 'Mina/Arafat',

            // Domestic
            self::KARACHI->value => 'Karachi',
            self::LAHORE->value => 'Lahore',
            self::ISLAMABAD->value => 'Islamabad',
            self::RAWALPINDI->value => 'Rawalpindi',
            self::PESHAWAR->value => 'Peshawar',
            self::MULTAN->value => 'Multan',
            self::FAISALABAD->value => 'Faisalabad',
            self::QUETTA->value => 'Quetta',
            self::SIALKOT->value => 'Sialkot',

            // International
            self::DUBAI->value => 'Dubai',
            self::ABU_DHABI->value => 'Abu Dhabi',
            self::SHARJAH->value => 'Sharjah',
            self::ISTANBUL->value => 'Istanbul',
            self::BAKU->value => 'Baku',
            self::LONDON->value => 'London',
            self::BANGKOK->value => 'Bangkok',
            self::KUALA_LUMPUR->value => 'Kuala Lumpur',
            self::DOHA->value => 'Doha',
        ];
    }
}
