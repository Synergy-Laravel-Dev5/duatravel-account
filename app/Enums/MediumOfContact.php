<?php

namespace App\Enums;

enum MediumOfContact: string
{
    case PHONE = 'phone';
    case WHATSAPP = 'whatsapp';
    case EMAIL = 'email';
    case OFFICE_VISIT = 'office_visit';
    case WEBSITE_FORM = 'website_form';
    case SOCIAL_MEDIA = 'social_media';

    public function label(): string
    {
        return match ($this) {
            self::PHONE => 'Phone',
            self::WHATSAPP => 'WhatsApp',
            self::EMAIL => 'Email',
            self::OFFICE_VISIT => 'Office Visit',
            self::WEBSITE_FORM => 'Website Form',
            self::SOCIAL_MEDIA => 'Social Media',
        };
    }
}
