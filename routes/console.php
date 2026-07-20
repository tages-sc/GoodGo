<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('competitions:update-statuses')->hourly();

// Drena la coda dei job (es. ProcessTrackJob) sfruttando il cron schedule:run,
// dato che su hosting condiviso non e' possibile tenere attivo un worker persistente.
// Il lock scade dopo 5 minuti: senza scadenza esplicita Laravel usa 24h, e un
// worker ucciso dall'hosting (limiti sui processi lunghi) bloccherebbe la coda
// per un giorno intero.
Schedule::command('queue:work --stop-when-empty --max-time=55')
    ->everyMinute()
    ->withoutOverlapping(5);
