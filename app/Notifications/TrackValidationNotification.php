<?php

namespace App\Notifications;

use App\Enums\TrackStatus;
use App\Models\Track;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TrackValidationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected Track $track,
        protected array $validationResult
    ) {}

    /**
     * Canali di notifica
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Notifica via email
     */
    public function toMail(object $notifiable): MailMessage
    {
        $isValid = $this->track->status === TrackStatus::VALID;

        $mail = (new MailMessage)
            ->subject($isValid
                ? 'La tua traccia è stata validata!'
                : 'Esito validazione traccia')
            ->greeting("Ciao {$notifiable->name}!");

        if ($isValid) {
            $mail->line('La tua traccia è stata validata con successo.')
                ->line('')
                ->line('**Riepilogo:**')
                ->line("- Distanza totale: {$this->formatDistance($this->track->total_distance_km)} km")
                ->line("- Distanza valida: {$this->formatDistance($this->track->valid_distance_km)} km")
                ->line("- Crediti guadagnati: {$this->formatCredits($this->track->credits_earned)}")
                ->line("- CO2 risparmiata: {$this->formatCo2($this->track->co2_saved_grams)}")
                ->line("- Calorie bruciate: {$this->formatCalories($this->track->calories_burned)}");

            if (!empty($this->validationResult['warnings'])) {
                $mail->line('')
                    ->line('**Note:**');
                foreach ($this->validationResult['warnings'] as $warning) {
                    $mail->line("- {$warning}");
                }
            }
        } else {
            $mail->line('Purtroppo la tua traccia non ha superato la validazione.')
                ->line('')
                ->line('**Motivo:**')
                ->line($this->track->rejection_reason ?? 'Errore durante l\'elaborazione');

            if (!empty($this->validationResult['warnings'])) {
                $mail->line('')
                    ->line('**Dettagli:**');
                foreach (array_slice($this->validationResult['warnings'], 0, 5) as $warning) {
                    $mail->line("- {$warning}");
                }
            }

            $mail->line('')
                ->line('Se ritieni che ci sia stato un errore, puoi contattare il supporto.')
                ->action('Contatta il supporto', url('/support'));
        }

        $mail->line('')
            ->line('Continua a muoverti in modo sostenibile!')
            ->salutation('Il team GoodGo');

        return $mail;
    }

    /**
     * Notifica per database
     */
    public function toArray(object $notifiable): array
    {
        $isValid = $this->track->status === TrackStatus::VALID;

        return [
            'type' => 'track_validation',
            'track_id' => $this->track->id,
            'is_valid' => $isValid,
            'title' => $isValid
                ? 'Traccia validata!'
                : 'Traccia non valida',
            'message' => $isValid
                ? "Hai guadagnato {$this->formatCredits($this->track->credits_earned)} crediti per {$this->formatDistance($this->track->valid_distance_km)} km percorsi."
                : ($this->track->rejection_reason ?? 'La traccia non ha superato la validazione.'),
            'credits_earned' => $this->track->credits_earned,
            'distance_km' => $this->track->valid_distance_km,
            'co2_saved_grams' => $this->track->co2_saved_grams,
            'warnings' => $this->validationResult['warnings'] ?? [],
        ];
    }

    /**
     * Formatta la distanza
     */
    protected function formatDistance(?float $km): string
    {
        return number_format($km ?? 0, 2, ',', '.');
    }

    /**
     * Formatta i crediti
     */
    protected function formatCredits(?float $credits): string
    {
        return number_format($credits ?? 0, 2, ',', '.');
    }

    /**
     * Formatta la CO2
     */
    protected function formatCo2(?float $grams): string
    {
        if (!$grams) {
            return '0 g';
        }

        if ($grams >= 1000) {
            return number_format($grams / 1000, 2, ',', '.') . ' kg';
        }

        return number_format($grams, 0, ',', '.') . ' g';
    }

    /**
     * Formatta le calorie
     */
    protected function formatCalories(?float $calories): string
    {
        return number_format($calories ?? 0, 0, ',', '.') . ' kcal';
    }
}
