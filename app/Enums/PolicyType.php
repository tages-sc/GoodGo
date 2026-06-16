<?php

namespace App\Enums;

enum PolicyType: string
{
    case PRIVACY = 'privacy';
    case TERMS = 'terms';

    public function label(): string
    {
        return match ($this) {
            self::PRIVACY => 'Privacy Policy',
            self::TERMS => 'Termini di Servizio',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
