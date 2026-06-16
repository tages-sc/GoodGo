<?php

namespace Database\Seeders;

use App\Models\Badge;
use Illuminate\Database\Seeder;

class BadgeSeeder extends Seeder
{
    public function run(): void
    {
        $badges = [
            // REGISTRAZIONE
            [
                'name' => 'Nuovo Utente',
                'slug' => 'nuovo-utente',
                'description' => 'Benvenuto e pronto a partire con una mobilità più sostenibile ed ecologica!! Condividi i tuoi risultati con gli amici e sfidali in competizioni personali!',
                'category' => 'registrazione',
                'stars' => 0,
                'threshold_type' => null,
                'threshold_value' => null,
                'sort_order' => 1,
            ],

            // TRACCE
            [
                'name' => 'Rilevatore in erba',
                'slug' => 'rilevatore-in-erba',
                'description' => 'Ottimo!! Hai iniziato a registrare i tuoi percorsi, così potrai collezionare nuovi badges e crescere il tuo punteggio!! Continua così! Le statistiche che ti forniamo ti mostreranno come puoi risparmiare tempo, soldi e migliorare la salute, muovendoti in modo sostenibile',
                'category' => 'tracce',
                'stars' => 0,
                'threshold_type' => 'count',
                'threshold_value' => 1,
                'sort_order' => 2,
            ],
            [
                'name' => 'Rilevatore',
                'slug' => 'rilevatore-1-stella',
                'description' => 'Hai registrato attività in ogni giorno di una settimana!! Stai diventando veramente sostenibile nei tuoi spostamenti!!',
                'category' => 'tracce',
                'stars' => 1,
                'threshold_type' => 'daily_streak_week',
                'threshold_value' => 7,
                'sort_order' => 3,
            ],

            // MODALITA
            [
                'name' => 'Biker',
                'slug' => 'biker-1-stella',
                'description' => 'Grande!! Hai utilizzato la bici almeno in tre giorni diversi questa settimana!!',
                'category' => 'modalita',
                'stars' => 1,
                'threshold_type' => 'bike_days_week',
                'threshold_value' => 3,
                'sort_order' => 4,
            ],
            [
                'name' => 'Mobilità Collettiva',
                'slug' => 'mobilita-collettiva-1-stella',
                'description' => 'Bravissimo!! Hai utilizzato per la prima volta il Trasporto Pubblico! Sei più sostenibile risparmiando soldi e tempo!',
                'category' => 'modalita',
                'stars' => 1,
                'threshold_type' => 'tpl_count',
                'threshold_value' => 1,
                'sort_order' => 5,
            ],
            [
                'name' => 'Mobilità Collettiva',
                'slug' => 'mobilita-collettiva-2-stelle',
                'description' => 'Hai fermato in garage la tua auto ed utilizzato il Trasporto Pubblico per almeno cinque volte!!',
                'category' => 'modalita',
                'stars' => 2,
                'threshold_type' => 'tpl_count',
                'threshold_value' => 5,
                'sort_order' => 6,
            ],

            // DISTANZA
            [
                'name' => 'Bike Surfer',
                'slug' => 'bike-surfer-1-stella',
                'description' => 'Un vero ciclista!! Hai appena superato percorrenze in bici per oltre 10 km',
                'category' => 'distanza',
                'stars' => 1,
                'threshold_type' => 'bike_distance_km',
                'threshold_value' => 10,
                'sort_order' => 7,
            ],
            [
                'name' => 'TPL Surfer',
                'slug' => 'tpl-surfer-1-stella',
                'description' => 'Ottimo!! Hai appena percorso 25 km totali con mezzi pubblici!! Stai dando un grande apporto alla mobilità sostenibile!',
                'category' => 'distanza',
                'stars' => 1,
                'threshold_type' => 'tpl_distance_km',
                'threshold_value' => 25,
                'sort_order' => 8,
            ],
            [
                'name' => 'Multi Surfer',
                'slug' => 'multi-surfer-1-stella',
                'description' => 'Grande!! Hai dimostrato una buona multimodalità percorrendo più di 100 km con più modalità sostenibili',
                'category' => 'distanza',
                'stars' => 1,
                'threshold_type' => 'multi_modal_distance_km',
                'threshold_value' => 100,
                'sort_order' => 9,
            ],
            [
                'name' => 'Multi Surfer',
                'slug' => 'multi-surfer-2-stelle',
                'description' => 'Grande!! Hai dimostrato una elevata mobilità multimodale percorrendo più di 250 km con più modalità sostenibili',
                'category' => 'distanza',
                'stars' => 2,
                'threshold_type' => 'multi_modal_distance_km',
                'threshold_value' => 250,
                'sort_order' => 10,
            ],

            // EMISSIONI
            [
                'name' => 'Ecologista',
                'slug' => 'ecologista-1-stella',
                'description' => 'Bravo! I tuoi spostamenti sostenibili hanno portato a risparmiare emissioni nocive per un totale di 25 kg di CO2',
                'category' => 'emissioni',
                'stars' => 1,
                'threshold_type' => 'co2_kg',
                'threshold_value' => 25,
                'sort_order' => 11,
            ],

            // SALUTE
            [
                'name' => 'Salutista',
                'slug' => 'salutista-1-stella',
                'description' => 'Perfetto!! Ti stai mettendo in forma!! Hai appena consumato 750 calorie con i tuoi spostamenti attivi e sostenibili',
                'category' => 'salute',
                'stars' => 1,
                'threshold_type' => 'calories',
                'threshold_value' => 750,
                'sort_order' => 12,
            ],
            [
                'name' => 'Salutista',
                'slug' => 'salutista-2-stelle',
                'description' => 'Ottimo!! Hai consumato 2.250 calorie grazie ai tuoi spostamenti. Non inquini e ti mantieni in forma!!',
                'category' => 'salute',
                'stars' => 2,
                'threshold_type' => 'calories',
                'threshold_value' => 2250,
                'sort_order' => 13,
            ],
            [
                'name' => 'Salutista',
                'slug' => 'salutista-3-stelle',
                'description' => 'Grande!! I tuoi spostamenti sostenibili ti hanno fatto consumare 4.500 calorie!',
                'category' => 'salute',
                'stars' => 3,
                'threshold_type' => 'calories',
                'threshold_value' => 4500,
                'sort_order' => 14,
            ],

            // ECONOMIA
            [
                'name' => 'Risparmiatore',
                'slug' => 'risparmiatore-1-stella',
                'description' => 'Hai risparmiato 6€ muovendoti senza la tua auto',
                'category' => 'economia',
                'stars' => 1,
                'threshold_type' => 'money_euro',
                'threshold_value' => 6,
                'sort_order' => 15,
            ],
            [
                'name' => 'Risparmiatore',
                'slug' => 'risparmiatore-2-stelle',
                'description' => 'Hai risparmiato 15€ muovendoti sostenibilmente! Inquini meno e risparmi denaro!!',
                'category' => 'economia',
                'stars' => 2,
                'threshold_type' => 'money_euro',
                'threshold_value' => 15,
                'sort_order' => 16,
            ],
            [
                'name' => 'Risparmiatore',
                'slug' => 'risparmiatore-3-stelle',
                'description' => 'Hai risparmiato 30€ muovendoti sostenibilmente! Inquini meno e risparmi denaro!!',
                'category' => 'economia',
                'stars' => 3,
                'threshold_type' => 'money_euro',
                'threshold_value' => 30,
                'sort_order' => 17,
            ],
        ];

        foreach ($badges as $badgeData) {
            Badge::updateOrCreate(
                ['slug' => $badgeData['slug']],
                $badgeData
            );
        }
    }
}
