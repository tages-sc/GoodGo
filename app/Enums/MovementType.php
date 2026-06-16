<?php

namespace App\Enums;

enum MovementType: string
{
    case EXPENSE = 'expense';         // Spesa presso un partner
    case REWARD = 'reward';           // Premio/ricompensa
    case REFUND = 'refund';           // Rimborso
    case ADJUSTMENT = 'adjustment';   // Rettifica manuale

    public function label(): string
    {
        return match ($this) {
            self::EXPENSE => 'Spesa',
            self::REWARD => 'Premio',
            self::REFUND => 'Rimborso',
            self::ADJUSTMENT => 'Rettifica',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::EXPENSE => 'Utilizzo crediti presso un partner commerciale',
            self::REWARD => 'Premio o ricompensa assegnata',
            self::REFUND => 'Rimborso di crediti',
            self::ADJUSTMENT => 'Rettifica manuale del saldo',
        };
    }

    /**
     * Indica se questo tipo sottrae crediti dall'utente
     */
    public function deductsCredits(): bool
    {
        return match ($this) {
            self::EXPENSE => true,
            self::REWARD => false,
            self::REFUND => false,
            self::ADJUSTMENT => false, // Dipende dal segno dell'importo
        };
    }

    /**
     * Indica se questo tipo richiede un partner
     */
    public function requiresPartner(): bool
    {
        return match ($this) {
            self::EXPENSE => true,
            self::REWARD => false,
            self::REFUND => false,
            self::ADJUSTMENT => false,
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::EXPENSE => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
            self::REWARD => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
            self::REFUND => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
            self::ADJUSTMENT => 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300',
        };
    }
}
