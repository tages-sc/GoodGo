<?php

namespace App\Services;

use App\Enums\CreditLogType;
use App\Enums\TrackStatus;
use App\Models\Track;
use App\Models\TrackValidationLog;
use App\Notifications\TrackValidationNotification;

class TrackValidationManager
{
    public function __construct(
        protected CreditService $creditService,
        protected TrackValidationService $validationService
    ) {}

    /**
     * Valida manualmente una traccia: ricalcola segmenti, metriche e crediti con le
     * regole della gara (TrackValidationService::validateManually), accredita o
     * storna la differenza rispetto ai crediti già assegnati e notifica l'utente.
     * Ritorna il delta crediti applicato.
     */
    public function validate(Track $track, ?int $validatedBy = null): float
    {
        $previousStatus = $track->status;
        // Solo una traccia già valida ha ricevuto crediti
        $previousCredits = $previousStatus === TrackStatus::VALID ? (float) $track->credits_earned : 0.0;

        $result = $this->validationService->validateManually($track);

        $track->refresh();
        $track->update([
            'validated_at' => now(),
            'validated_by' => $validatedBy,
        ]);

        $creditsDelta = round((float) $track->credits_earned - $previousCredits, 4);

        if ($creditsDelta > 0) {
            $this->creditService->addCredits(
                $track->user,
                $creditsDelta,
                CreditLogType::TRACK_VALIDATION,
                "Validazione manuale traccia #{$track->id}",
                $track
            );
        } elseif ($creditsDelta < 0) {
            try {
                $this->creditService->subtractCredits(
                    $track->user,
                    abs($creditsDelta),
                    CreditLogType::MANUAL_SUBTRACT,
                    "Ricalcolo crediti in validazione manuale traccia #{$track->id}",
                    $track
                );
            } catch (\InvalidArgumentException $e) {
                // Saldo insufficiente (crediti già spesi): nessuno storno
                $creditsDelta = 0.0;
            }
        }

        if ($previousStatus !== TrackStatus::VALID || $creditsDelta != 0) {
            $track->user->notify(new TrackValidationNotification($track, [
                'warnings' => $result['warnings'] ?? [],
            ]));
        }

        TrackValidationLog::create([
            'track_id' => $track->id,
            'previous_status' => $previousStatus,
            'new_status' => $track->status,
            'reason' => null,
            'credits_delta' => $creditsDelta,
            'changed_by' => $validatedBy,
        ]);

        return $creditsDelta;
    }

    /**
     * Invalida una traccia: sottrae eventuali crediti precedenti,
     * aggiorna stato, invalida segmenti e notifica l'utente.
     * Ritorna il delta crediti rimossi (>= 0).
     */
    public function invalidate(Track $track, string $reason, ?int $validatedBy = null): float
    {
        $previousStatus = $track->status;
        $previousCredits = (float) $track->credits_earned;
        $creditsDelta = 0.0;

        if ($previousStatus === TrackStatus::VALID && $previousCredits > 0) {
            try {
                $this->creditService->subtractCredits(
                    $track->user,
                    $previousCredits,
                    CreditLogType::MANUAL_SUBTRACT,
                    "Invalidazione manuale traccia #{$track->id}: {$reason}",
                    $track
                );
                $creditsDelta = -$previousCredits;
            } catch (\InvalidArgumentException $e) {
                $this->creditService->addCredits(
                    $track->user,
                    0.0001,
                    CreditLogType::ADJUSTMENT,
                    "Rettifica per invalidazione traccia #{$track->id} - crediti insufficienti per sottrazione completa",
                    $track
                );
            }
        }

        $track->markAsInvalid($reason, $validatedBy);

        foreach ($track->segments as $segment) {
            $segment->markAsInvalid($reason);
        }

        $track->refresh();

        if ($previousStatus !== TrackStatus::INVALID) {
            $track->user->notify(new TrackValidationNotification($track, [
                'warnings' => $previousCredits > 0
                    ? ["I crediti precedentemente assegnati ({$previousCredits}) sono stati rimossi."]
                    : [],
            ]));
        }

        TrackValidationLog::create([
            'track_id' => $track->id,
            'previous_status' => $previousStatus,
            'new_status' => TrackStatus::INVALID,
            'reason' => $reason,
            'credits_delta' => $creditsDelta,
            'changed_by' => $validatedBy,
        ]);

        return $previousStatus === TrackStatus::VALID ? $previousCredits : 0.0;
    }
}
