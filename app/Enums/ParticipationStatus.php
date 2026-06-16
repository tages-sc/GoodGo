<?php

namespace App\Enums;

enum ParticipationStatus: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case WITHDRAWN = 'withdrawn';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'In attesa',
            self::APPROVED => 'Approvato',
            self::REJECTED => 'Rifiutato',
            self::WITHDRAWN => 'Ritirato',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::PENDING => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
            self::APPROVED => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
            self::REJECTED => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
            self::WITHDRAWN => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
        };
    }

    /**
     * Colore indicatore stato (per documentazione: verde/arancione/rosso)
     */
    public function dotColor(): string
    {
        return match ($this) {
            self::PENDING => 'bg-yellow-400',
            self::APPROVED => 'bg-green-400',
            self::REJECTED => 'bg-red-400',
            self::WITHDRAWN => 'bg-gray-400',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Stati che permettono cambio
     */
    public static function changeable(): array
    {
        return [self::PENDING, self::APPROVED];
    }

    /**
     * Verifica se lo stato può essere cambiato
     */
    public function canChange(): bool
    {
        return in_array($this, self::changeable());
    }
}
