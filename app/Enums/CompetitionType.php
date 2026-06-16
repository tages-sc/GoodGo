<?php

namespace App\Enums;

/**
 * Tipo di gara
 */
enum CompetitionType: string
{
    case MUNICIPAL = 'municipal';       // Comunale
    case PROVINCIAL = 'provincial';     // Provinciale
    case REGIONAL = 'regional';         // Regionale
    case NATIONAL = 'national';         // Nazionale
    case CORPORATE = 'corporate';       // Aziendale

    public function label(): string
    {
        return match ($this) {
            self::MUNICIPAL => 'Comunale',
            self::PROVINCIAL => 'Provinciale',
            self::REGIONAL => 'Regionale',
            self::NATIONAL => 'Nazionale',
            self::CORPORATE => 'Aziendale',
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
