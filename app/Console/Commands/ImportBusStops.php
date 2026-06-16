<?php

namespace App\Console\Commands;

use App\Models\BusStop;
use Illuminate\Console\Command;

class ImportBusStops extends Command
{
    protected $signature = 'import:bus-stops
                            {--path= : Percorso del CSV (default: docs/CSVs/CSV_PALINE_BUS.csv)}
                            {--truncate : Svuota la tabella prima dell\'import}';

    protected $description = 'Importa le fermate bus da CSV (header: stop_name,stop_lat,stop_lon)';

    private const BATCH_SIZE = 1000;

    public function handle(): int
    {
        $path = $this->option('path') ?: base_path('docs/CSVs/CSV_PALINE_BUS.csv');

        if (!is_file($path) || !is_readable($path)) {
            $this->error("File non trovato o non leggibile: {$path}");
            return self::FAILURE;
        }

        if ($this->option('truncate')) {
            BusStop::truncate();
            $this->warn('Tabella bus_stops svuotata.');
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

            // Header CSV: stop_name, stop_lat, stop_lon
            $name = isset($row[0]) ? trim($row[0]) : '';
            $lat = $this->parseFloat($row[1] ?? null);
            $lng = $this->parseFloat($row[2] ?? null);

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
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if (count($batch) >= self::BATCH_SIZE) {
                BusStop::insert($batch);
                $inserted += count($batch);
                $batch = [];
            }
        }

        if (!empty($batch)) {
            BusStop::insert($batch);
            $inserted += count($batch);
        }

        fclose($handle);
        $progress->finish();
        $this->newLine(2);

        $this->info("Righe processate:       {$processed}");
        $this->info("Fermate inserite:       {$inserted}");
        $this->info("Duplicate saltate:      {$skippedDuplicate}");
        $this->info("Righe invalide saltate: {$skippedInvalid}");

        return self::SUCCESS;
    }

    private function loadExistingKeys(): array
    {
        $existing = [];
        BusStop::select('latitude', 'longitude')
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
        $s = str_replace(',', '.', $s);
        return is_numeric($s) ? (float) $s : null;
    }

    private function validCoord(float $lat, float $lng): bool
    {
        return $lat >= -90.0 && $lat <= 90.0 && $lng >= -180.0 && $lng <= 180.0;
    }
}
