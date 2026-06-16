<?php

namespace App\Enums;

enum MovementStatus: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'In attesa',
            self::APPROVED => 'Approvato',
            self::REJECTED => 'Rifiutato',
            self::CANCELLED => 'Annullato',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::PENDING => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
            self::APPROVED => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
            self::REJECTED => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
            self::CANCELLED => 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300',
        };
    }

    /**
     * Stati che possono essere processati
     */
    public static function processable(): array
    {
        return [self::PENDING];
    }

    /**
     * Stati finali (non modificabili)
     */
    public static function final(): array
    {
        return [self::APPROVED, self::REJECTED, self::CANCELLED];
    }

    public function isFinal(): bool
    {
        return in_array($this, self::final());
    }

    public function canBeProcessed(): bool
    {
        return in_array($this, self::processable());
    }
}
