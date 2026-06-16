<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('competitions:update-statuses')->hourly();

// Drena la coda dei job (es. ProcessTrackJob) sfruttando il cron schedule:run,
// dato che su hosting condiviso non e' possibile tenere attivo un worker persistente.
Schedule::command('queue:work --stop-when-empty --max-time=55')
    ->everyMinute()
    ->withoutOverlapping();
