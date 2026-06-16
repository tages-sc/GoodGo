<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\Municipality;
use App\Models\Province;
use App\Models\Region;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GeographicDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Importazione dati geografici italiani...');

        // Crea Italia
        $italy = Country::firstOrCreate(
            ['code' => 'ITA'],
            [
                'name' => 'Italia',
                'code_2' => 'IT',
                'is_active' => true,
            ]
        );

        $this->command->info('Paese Italia creato.');

        // Leggi il CSV
        $csvPath = base_path('docs/Lista_Com_Prov_Reg.csv');

        if (! file_exists($csvPath)) {
            $this->command->error("File CSV non trovato: {$csvPath}");
            return;
        }

        $handle = fopen($csvPath, 'r');
        if (! $handle) {
            $this->command->error("Impossibile aprire il file CSV.");
            return;
        }

        // Cache per evitare query duplicate
        $regions = [];
        $provinces = [];
        $municipalityCount = 0;

        // Salta l'header
        fgetcsv($handle, 0, ';');

        DB::beginTransaction();

        try {
            while (($data = fgetcsv($handle, 0, ';')) !== false) {
                // Formato: name;prov_name;reg_name;;
                $municipalityName = trim($data[0] ?? '');
                $provinceName = trim($data[1] ?? '');
                $regionName = trim($data[2] ?? '');

                if (empty($municipalityName) || empty($provinceName) || empty($regionName)) {
                    continue;
                }

                // Crea/recupera regione
                if (! isset($regions[$regionName])) {
                    $regions[$regionName] = Region::firstOrCreate(
                        ['country_id' => $italy->id, 'name' => $regionName],
                        ['is_active' => true]
                    );
                }
                $region = $regions[$regionName];

                // Crea/recupera provincia
                $provinceKey = "{$regionName}_{$provinceName}";
                if (! isset($provinces[$provinceKey])) {
                    $provinces[$provinceKey] = Province::firstOrCreate(
                        ['region_id' => $region->id, 'name' => $provinceName],
                        ['is_active' => true]
                    );
                }
                $province = $provinces[$provinceKey];

                // Crea comune
                Municipality::firstOrCreate(
                    ['province_id' => $province->id, 'name' => $municipalityName],
                    ['is_active' => true]
                );

                $municipalityCount++;

                // Progress ogni 500 comuni
                if ($municipalityCount % 500 === 0) {
                    $this->command->info("  Importati {$municipalityCount} comuni...");
                }
            }

            DB::commit();

            fclose($handle);

            $this->command->info('');
            $this->command->info('Importazione completata:');
            $this->command->info("  - Regioni: " . count($regions));
            $this->command->info("  - Province: " . count($provinces));
            $this->command->info("  - Comuni: {$municipalityCount}");

        } catch (\Exception $e) {
            DB::rollBack();
            fclose($handle);
            $this->command->error("Errore durante l'importazione: " . $e->getMessage());
        }
    }
}
