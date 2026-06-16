<?php

namespace App\Enums;

/**
 * Tipo di punteggio per la classifica della gara
 */
enum ScoringType: string
{
    case DISTANCE = 'distance';     // Basato sulla distanza percorsa
    case CO2_SAVED = 'co2_saved';   // Basato sulle emissioni CO2 risparmiate

    public function label(): string
    {
        return match ($this) {
            self::DISTANCE => 'Distanza percorsa',
            self::CO2_SAVED => 'Emissioni CO2 risparmiate',
        };
    }

    public function unit(): string
    {
        return match ($this) {
            self::DISTANCE => 'km',
            self::CO2_SAVED => 'kg CO2',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn($case) => [
            $case->value => $case->label()
        ])->toArray();
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
