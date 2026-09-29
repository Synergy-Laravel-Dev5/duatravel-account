<?php

namespace App\Enums;

enum CurrencyType: string
{
    case PKR = 'PKR';
    case SAR = 'SAR';
    case USD = 'USD';
    case GBP = 'GBP';
    case AED = 'AED';

    public static function options(): array
    {
        return [
            self::PKR->value => 'PKR - Pakistan',
            self::SAR->value => 'SAR - Saudi Arabia',
            self::USD->value => 'USD - United States',
            self::GBP->value => 'GBP - Great Britain',
            self::AED->value => 'AED - United Arab Emirates',
        ];
    }
}