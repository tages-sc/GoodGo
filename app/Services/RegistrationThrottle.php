<?php

namespace App\Services;

use Illuminate\Support\Facades\RateLimiter;

/**
 * Limita il numero di registrazioni completate per indirizzo IP.
 *
 * Condiviso da registrazione web (Fortify), registrazione partner (Livewire)
 * e API /signup. Conta solo le registrazioni andate a buon fine, così un
 * utente che sbaglia la validazione non viene bloccato.
 */
class RegistrationThrottle
{
    private const LIMITS = [
        'minute' => ['max' => 3, 'decay' => 60],
        'hour' => ['max' => 20, 'decay' => 3600],
    ];

    /**
     * Secondi di attesa se l'IP ha superato un limite, altrimenti 0.
     */
    public static function availableIn(?string $ip): int
    {
        foreach (self::LIMITS as $window => $limit) {
            $key = self::key($ip, $window);

            if (RateLimiter::tooManyAttempts($key, $limit['max'])) {
                return RateLimiter::availableIn($key);
            }
        }

        return 0;
    }

    /**
     * Registra una registrazione completata per l'IP.
     */
    public static function hit(?string $ip): void
    {
        foreach (self::LIMITS as $window => $limit) {
            RateLimiter::hit(self::key($ip, $window), $limit['decay']);
        }
    }

    public static function message(int $seconds): string
    {
        return 'Troppe registrazioni da questo indirizzo. Riprova tra ' . ceil($seconds / 60) . ' minuti.';
    }

    private static function key(?string $ip, string $window): string
    {
        return "registration:{$window}:" . ($ip ?? 'unknown');
    }
}
