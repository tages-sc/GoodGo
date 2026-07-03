<?php

use Illuminate\Support\Carbon;

if (! function_exists('local_dt')) {
    /**
     * Formatta un istante (timestamp) nel fuso di visualizzazione (Europe/Rome).
     *
     * I timestamp vengono salvati in UTC: questo helper li converte al fuso
     * definito in config('app.display_timezone') solo per mostrarli all'utente.
     *
     * Accetta un Carbon/DateTimeInterface oppure una stringa (es. valori pivot
     * non castati). Restituisce '' se il valore e' vuoto.
     *
     * NB: usare SOLO per campi che rappresentano un istante (created_at,
     * started_at, processed_at, ...). NON usare per date di calendario
     * (birth_date, start_date/end_date gara), che non vanno convertite.
     *
     * @param  \DateTimeInterface|string|null  $value
     */
    function local_dt($value, string $format = 'd/m/Y H:i'): string
    {
        if (blank($value)) {
            return '';
        }

        return Carbon::parse($value)
            ->timezone(config('app.display_timezone', 'Europe/Rome'))
            ->format($format);
    }
}
