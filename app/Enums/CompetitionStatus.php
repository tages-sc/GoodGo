<?php

namespace App\Enums;

enum CompetitionStatus: string
{
    case DRAFT = 'draft';
    case PUBLISHED = 'published';
    case ACTIVE = 'active';
    case ENDED = 'ended';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Bozza',
            self::PUBLISHED => 'Pubblicata',
            self::ACTIVE => 'In corso',
            self::ENDED => 'Terminata',
            self::CANCELLED => 'Annullata',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::DRAFT => 'gray',
            self::PUBLISHED => 'blue',
            self::ACTIVE => 'green',
            self::ENDED => 'indigo',
            self::CANCELLED => 'red',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::DRAFT => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
            self::PUBLISHED => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
            self::ACTIVE => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
            self::ENDED => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-300',
            self::CANCELLED => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Stati che permettono l'iscrizione degli utenti
     */
    public static function subscribableStatuses(): array
    {
        return [self::PUBLISHED, self::ACTIVE];
    }

    /**
     * Stati che permettono l'upload di tracce
     */
    public static function trackUploadStatuses(): array
    {
        return [self::ACTIVE];
    }
}
