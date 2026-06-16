<?php

namespace App\Jobs;

use App\Models\Track;
use App\Notifications\TrackValidationNotification;
use App\Services\TrackValidationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessTrackJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Numero massimo di tentativi
     */
    public int $tries = 3;

    /**
     * Timeout in secondi
     */
    public int $timeout = 120;

    /**
     * Secondi da aspettare prima di un nuovo tentativo
     */
    public int $backoff = 30;

    public function __construct(
        public Track $track
    ) {}

    /**
     * Esegue il job
     */
    public function handle(TrackValidationService $validationService): void
    {
        Log::channel('tracks')->info("ProcessTrackJob: Inizio elaborazione traccia #{$this->track->id}");

        try {
            // Imposta la traccia come in elaborazione
            $this->track->markAsProcessing();

            // Esegue la validazione completa
            $result = $validationService->validate($this->track);

            // Invia notifica all'utente
            if ($this->track->user) {
                $this->track->user->notify(new TrackValidationNotification($this->track, $result));
            }

            Log::channel('tracks')->info("ProcessTrackJob: Traccia #{$this->track->id} elaborata con successo", [
                'status' => $this->track->status->value,
                'valid_distance_km' => $this->track->valid_distance_km,
                'credits_earned' => $this->track->credits_earned,
            ]);

        } catch (\Exception $e) {
            Log::channel('tracks')->error("ProcessTrackJob: Errore elaborazione traccia #{$this->track->id}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Se l'errore persiste, marca come invalida
            if ($this->attempts() >= $this->tries) {
                $this->track->markAsInvalid('Errore durante l\'elaborazione: ' . $e->getMessage());
            }

            throw $e;
        }
    }

    /**
     * Gestisce il fallimento del job
     */
    public function failed(\Throwable $exception): void
    {
        Log::channel('tracks')->error("ProcessTrackJob: Fallimento definitivo traccia #{$this->track->id}", [
            'error' => $exception->getMessage(),
        ]);

        $this->track->markAsInvalid('Elaborazione fallita dopo ' . $this->tries . ' tentativi.');
    }

    /**
     * Tag per identificare il job
     */
    public function tags(): array
    {
        return [
            'track:' . $this->track->id,
            'user:' . $this->track->user_id,
        ];
    }
}
