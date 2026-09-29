<?php

namespace App\Enums;

enum TravelRoute: string
{
    case KHI_JED_KHI = 'KHI-JED-KHI';
    case KHI_MED_JED_KHI = 'KHI-MED-JED-KHI';
    case LHE_JED_LHE = 'LHE-JED-LHE';
    case ISB_JED_ISB = 'ISB-JED-ISB';
    case KHI_JED_MED_KHI = 'KHI-JED-MED-KHI';

    public static function options(): array
    {
        return [
            self::KHI_JED_KHI->value => 'Karachi - Jeddah - Karachi (KHI-JED-KHI)',
            self::KHI_MED_JED_KHI->value => 'Karachi - Madinah / Jeddah - Karachi (KHI-MED-JED-KHI)',
            self::LHE_JED_LHE->value => 'Lahore - Jeddah - Lahore (LHE-JED-LHE)',
            self::ISB_JED_ISB->value => 'Islamabad - Jeddah - Islamabad (ISB-JED-ISB)',
            self::KHI_JED_MED_KHI->value => 'Karachi - Jeddah - Madinah - Karachi (KHI-JED-MED-KHI)',
        ];
    }
}
