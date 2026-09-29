<?php

namespace App\Enums;

enum LeadSource: string
{
    case WEBSITE = 'website';
    case FACEBOOK = 'facebook';
    case INSTAGRAM = 'instagram';
    case GOOGLE = 'google';
    case REFERRAL = 'referral';
    case WALK_IN = 'walk_in';
    case AGENT = 'agent';

    public function label(): string
    {
        return match ($this) {
            self::WEBSITE => 'Website',
            self::FACEBOOK => 'Facebook',
            self::INSTAGRAM => 'Instagram',
            self::GOOGLE => 'Google',
            self::REFERRAL => 'Referral',
            self::WALK_IN => 'Walk In',
            self::AGENT => 'Agent',
        };
    }
}
