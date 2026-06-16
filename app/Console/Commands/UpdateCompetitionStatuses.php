<?php

namespace App\Console\Commands;

use App\Enums\CompetitionStatus;
use App\Models\Competition;
use Illuminate\Console\Command;

class UpdateCompetitionStatuses extends Command
{
    protected $signature = 'competitions:update-statuses';

    protected $description = 'Aggiorna lo stato delle gare in base alle date di inizio e fine';

    public function handle(): int
    {
        $today = now()->toDateString();

        // Pubblicata → In corso (start_date <= oggi)
        $activated = Competition::where('status', CompetitionStatus::PUBLISHED)
            ->where('start_date', '<=', $today)
            ->update(['status' => CompetitionStatus::ACTIVE]);

        if ($activated > 0) {
            $this->info("Gare attivate: {$activated}");
        }

        // In corso → Terminata (end_date < oggi)
        $ended = Competition::where('status', CompetitionStatus::ACTIVE)
            ->where('end_date', '<', $today)
            ->update(['status' => CompetitionStatus::ENDED]);

        if ($ended > 0) {
            $this->info("Gare terminate: {$ended}");
        }

        if ($activated === 0 && $ended === 0) {
            $this->info('Nessuna gara da aggiornare.');
        }

        return self::SUCCESS;
    }
}
