<?php

namespace App\Console\Commands;

use App\Models\Competition;
use Illuminate\Console\Command;

class RefreshCompetitionLeaderboards extends Command
{
    protected $signature = 'competitions:refresh-leaderboards {competition? : ID della gara (tutte se omesso)}';

    protected $description = 'Ricalcola dalle tracce i totali di classifica, le statistiche e le posizioni delle gare non terminate';

    public function handle(): int
    {
        $query = Competition::query();

        if ($this->argument('competition')) {
            $query->whereKey($this->argument('competition'));
        }

        $count = 0;
        foreach ($query->cursor() as $competition) {
            if ($competition->isLeaderboardFrozen()) {
                $this->line("Gara #{$competition->id} {$competition->name}: terminata, classifica definitiva non modificata");
                continue;
            }

            $competition->refreshLeaderboard();
            $this->line("Gara #{$competition->id} {$competition->name}: {$competition->participants_count} iscritti approvati, {$competition->tracks_count} tracce");
            $count++;
        }

        $this->info("Classifiche ricalcolate: {$count}");

        return self::SUCCESS;
    }
}
