<?php

namespace App\Enums;

/**
 * Modalita di gara per assegnazione premi
 *
 * IMPORTANTE: I partner possono iscriversi SOLO a gare con modalita CREDITS_BASED
 */
enum RewardMode: string
{
    case RANKING = 'ranking';               // Graduatoria assoluta - premi basati su posizione classifica
    case CREDITS_BASED = 'credits_based';   // Premi basati su crediti - crediti spendibili presso partner

    public function label(): string
    {
        return match ($this) {
            self::RANKING => 'Graduatoria assoluta',
            self::CREDITS_BASED => 'Premi basati su crediti',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::RANKING => 'Gli utenti guadagnano crediti ma il valore in EUR e 0. La gara premia in base alla graduatoria con premi specifici.',
            self::CREDITS_BASED => 'Gli utenti guadagnano crediti e possono spenderli con richieste di movimento presso i partner.',
        };
    }

    /**
     * Verifica se i partner possono iscriversi a gare con questa modalita
     */
    public function allowsPartners(): bool
    {
        return $this === self::CREDITS_BASED;
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

    /**
     * Modalita che permettono l'iscrizione dei partner
     */
    public static function partnerAllowedModes(): array
    {
        return [self::CREDITS_BASED];
    }
}
