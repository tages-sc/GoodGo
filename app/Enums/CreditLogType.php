<?php

namespace App\Enums;

enum CreditLogType: string
{
    case TRACK_VALIDATION = 'track_validation';     // Crediti da validazione traccia
    case MANUAL_ADD = 'manual_add';                 // Aggiunta manuale
    case MANUAL_SUBTRACT = 'manual_subtract';       // Sottrazione manuale
    case MOVEMENT_EXPENSE = 'movement_expense';     // Spesa presso partner
    case MOVEMENT_REFUND = 'movement_refund';       // Rimborso movimento
    case MOVEMENT_REWARD = 'movement_reward';       // Premio/ricompensa
    case ADJUSTMENT = 'adjustment';                 // Rettifica
    case INITIAL = 'initial';                       // Saldo iniziale
    case BONUS = 'bonus';                           // Bonus (es. iscrizione)
    case EXPIRATION = 'expiration';                 // Scadenza crediti

    public function label(): string
    {
        return match ($this) {
            self::TRACK_VALIDATION => 'Validazione traccia',
            self::MANUAL_ADD => 'Aggiunta manuale',
            self::MANUAL_SUBTRACT => 'Sottrazione manuale',
            self::MOVEMENT_EXPENSE => 'Spesa',
            self::MOVEMENT_REFUND => 'Rimborso',
            self::MOVEMENT_REWARD => 'Premio',
            self::ADJUSTMENT => 'Rettifica',
            self::INITIAL => 'Saldo iniziale',
            self::BONUS => 'Bonus',
            self::EXPIRATION => 'Scadenza',
        };
    }

    /**
     * Indica se è un'operazione di aggiunta
     */
    public function isAddition(): bool
    {
        return in_array($this, [
            self::TRACK_VALIDATION,
            self::MANUAL_ADD,
            self::MOVEMENT_REFUND,
            self::MOVEMENT_REWARD,
            self::INITIAL,
            self::BONUS,
        ]);
    }

    /**
     * Indica se è un'operazione di sottrazione
     */
    public function isSubtraction(): bool
    {
        return in_array($this, [
            self::MANUAL_SUBTRACT,
            self::MOVEMENT_EXPENSE,
            self::EXPIRATION,
        ]);
    }

    public function badgeClasses(): string
    {
        if ($this->isAddition()) {
            return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300';
        }

        if ($this->isSubtraction()) {
            return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300';
        }

        return 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300';
    }
}
