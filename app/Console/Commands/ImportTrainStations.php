<?php

namespace App\Console\Commands;

use App\Models\TrainStation;
use Illuminate\Console\Command;

class ImportTrainStations extends Command
{
    protected $signature = 'import:train-stations
                            {--path= : Percorso del CSV (default: docs/CSVs/CSV_STAZIONI_FS.csv)}
                            {--truncate : Svuota la tabella prima dell\'import}';

    protected $description = 'Importa le stazioni ferroviarie da CSV (header: name,LONG,LAT)';

    private const BATCH_SIZE = 1000;

    public function handle(): int
    {
        $path = $this->option('path') ?: base_path('docs/CSVs/CSV_STAZIONI_FS.csv');

        if (!is_file($path) || !is_readable($path)) {
            $this->error("File non trovato o non leggibile: {$path}");
            return self::FAILURE;
        }

        if ($this->option('truncate')) {
            TrainStation::truncate();
            $this->warn('Tabella train_stations svuotata.');
        }

        $existing = $this->loadExistingKeys();

        $handle = fopen($path, 'r');
        if ($handle === false) {
            $this->error("Impossibile aprire il file: {$path}");
            return self::FAILURE;
        }

        // Salta header
        fgetcsv($handle);

        $inserted = 0;
        $skippedDuplicate = 0;
        $skippedInvalid = 0;
        $processed = 0;
        $batch = [];
        $seenInCsv = [];
        $now = now();

        $this->info('Import in corso...');
        $progress = $this->output->createProgressBar();
        $progress->start();

        while (($row = fgetcsv($handle)) !== false) {
            $processed++;
            $progress->advance();

            // Header CSV: name, LONG, LAT
            $name = isset($row[0]) ? trim($row[0]) : '';
            $lng = $this->parseFloat($row[1] ?? null);
            $lat = $this->parseFloat($row[2] ?? null);

            if ($name === '' || $lat === null || $lng === null || !$this->validCoord($lat, $lng)) {
                $skippedInvalid++;
                continue;
            }

            $lat = round($lat, 7);
            $lng = round($lng, 7);
            $key = $this->coordKey($lat, $lng);

            if (isset($seenInCsv[$key]) || isset($existing[$key])) {
                $skippedDuplicate++;
                continue;
            }
            $seenInCsv[$key] = true;

            $batch[] = [
                'name' => $name,
                'latitude' => $lat,
                'longitude' => $lng,
                'is_active' => true,
                'type' => 'regional',
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if (count($batch) >= self::BATCH_SIZE) {
                TrainStation::insert($batch);
                $inserted += count($batch);
                $batch = [];
            }
        }

        if (!empty($batch)) {
            TrainStation::insert($batch);
            $inserted += count($batch);
        }

        fclose($handle);
        $progress->finish();
        $this->newLine(2);

        $this->info("Righe processate:       {$processed}");
        $this->info("Stazioni inserite:      {$inserted}");
        $this->info("Duplicate saltate:      {$skippedDuplicate}");
        $this->info("Righe invalide saltate: {$skippedInvalid}");

        return self::SUCCESS;
    }

    private function loadExistingKeys(): array
    {
        $existing = [];
        TrainStation::select('latitude', 'longitude')
            ->chunk(5000, function ($rows) use (&$existing) {
                foreach ($rows as $r) {
                    $existing[$this->coordKey((float) $r->latitude, (float) $r->longitude)] = true;
                }
            });
        return $existing;
    }

    private function coordKey(float $lat, float $lng): string
    {
        return number_format($lat, 7, '.', '') . '|' . number_format($lng, 7, '.', '');
    }

    private function parseFloat(mixed $value): ?float
    {
        if ($value === null) {
            return null;
        }
        $s = trim((string) $value);
        if ($s === '') {
            return null;
        }
        // Accetta virgola come separatore decimale
        $s = str_replace(',', '.', $s);
        return is_numeric($s) ? (float) $s : null;
    }

    private function validCoord(float $lat, float $lng): bool
    {
        return $lat >= -90.0 && $lat <= 90.0 && $lng >= -180.0 && $lng <= 180.0;
    }
}
