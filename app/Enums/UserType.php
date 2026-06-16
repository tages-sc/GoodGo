<?php

namespace App\Enums;

enum UserType: string
{
    case SUPER_ADMIN = 'super_admin';
    case ENTE = 'ente';
    case ORGANIZER = 'organizer';
    case PARTNER = 'partner';
    case USER = 'user';

    public function label(): string
    {
        return match ($this) {
            self::SUPER_ADMIN => 'Super Admin',
            self::ENTE => 'Ente',
            self::ORGANIZER => 'Organizzatore Gara',
            self::PARTNER => 'Partner Commerciale',
            self::USER => 'Utente',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::SUPER_ADMIN => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
            self::ENTE => 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300',
            self::ORGANIZER => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
            self::PARTNER => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
            self::USER => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
        };
    }
}
