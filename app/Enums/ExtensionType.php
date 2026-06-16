<?php

namespace App\Enums;

/**
 * Estensione territoriale della gara
 */
enum ExtensionType: string
{
    case MUNICIPAL = 'municipal';       // Comunale
    case PROVINCIAL = 'provincial';     // Provinciale
    case REGIONAL = 'regional';         // Regionale
    case NATIONAL = 'national';         // Nazionale

    public function label(): string
    {
        return match ($this) {
            self::MUNICIPAL => 'Comunale',
            self::PROVINCIAL => 'Provinciale',
            self::REGIONAL => 'Regionale',
            self::NATIONAL => 'Nazionale',
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
