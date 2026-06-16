<?php

namespace App\Enums;

enum BadgeCategory: string
{
    case REGISTRAZIONE = 'registrazione';
    case TRACCE = 'tracce';
    case MODALITA = 'modalita';
    case DISTANZA = 'distanza';
    case EMISSIONI = 'emissioni';
    case SALUTE = 'salute';
    case ECONOMIA = 'economia';

    public function label(): string
    {
        return match ($this) {
            self::REGISTRAZIONE => 'Registrazione',
            self::TRACCE => 'Tracce',
            self::MODALITA => 'Modalità',
            self::DISTANZA => 'Distanza',
            self::EMISSIONI => 'Emissioni',
            self::SALUTE => 'Salute',
            self::ECONOMIA => 'Economia',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::REGISTRAZIONE => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
            self::TRACCE => 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300',
            self::MODALITA => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-300',
            self::DISTANZA => 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300',
            self::EMISSIONI => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
            self::SALUTE => 'bg-pink-100 text-pink-800 dark:bg-pink-900 dark:text-pink-300',
            self::ECONOMIA => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
        };
    }
}
