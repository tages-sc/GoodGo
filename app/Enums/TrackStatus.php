<?php

namespace App\Enums;

enum TrackStatus: string
{
    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case VALID = 'valid';
    case INVALID = 'invalid';
    case NOT_CREDITED = 'not_credited';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'In attesa',
            self::PROCESSING => 'In elaborazione',
            self::VALID => 'Valida',
            self::INVALID => 'Non valida',
            self::NOT_CREDITED => 'Non accreditata',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'gray',
            self::PROCESSING => 'blue',
            self::VALID => 'green',
            self::INVALID => 'red',
            self::NOT_CREDITED => 'yellow',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::PENDING => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
            self::PROCESSING => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
            self::VALID => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
            self::INVALID => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
            self::NOT_CREDITED => 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300',
        };
    }
}
