<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('competitions', function (Blueprint $table) {
            // Documento PDF generico opzionale
            $table->string('extra_document')->nullable()->after('rules');

            // Estensione territoriale (comunale, provinciale, regionale, nazionale)
            $table->string('extension_type')->nullable()->after('moderated_subscription');

            // Tipo di gara (aziendale, comunale, provinciale, regionale, nazionale)
            $table->string('competition_type')->nullable()->after('extension_type');

            // Modalita di gara: ranking = graduatoria assoluta, credits_based = premi basati su crediti
            // IMPORTANTE: I partner possono iscriversi SOLO a gare con reward_mode = 'credits_based'
            $table->string('reward_mode')->default('credits_based')->after('competition_type');

            // Link a questionario esterno (opzionale)
            $table->string('questionnaire_url')->nullable()->after('reward_mode');

            // Range di eta ammessi (JSON array: ['<19', '19-30', '30-65', '65+'])
            $table->json('age_range')->nullable()->after('questionnaire_url');

            // Tipo di punteggio per classifica: co2_saved = emissioni risparmiate, distance = distanza
            $table->string('scoring_type')->default('distance')->after('age_range');

            // Numero di crediti per formare 1 EUR (es: 100 crediti = 1 EUR)
            $table->decimal('credits_to_euro', 10, 2)->default(0)->after('credits_multiplier');

            // Crediti per km per ogni tipo di trasporto (JSON)
            // Es: {"train": 2, "bus": 2, "bike": 3, "walk": 4, "car": 0, "motorcycle": 0}
            $table->json('credits_per_mode')->nullable()->after('credits_to_euro');

            // Distanza massima per traccia per tipo di trasporto (JSON, in km)
            // Es: {"train": null, "bus": null, "bike": 50, "walk": 20, "car": null, "motorcycle": null}
            // null = senza limiti
            $table->json('max_distance_per_mode')->nullable()->after('max_track_distance');

            // Distanza massima giornaliera per tipo di trasporto (JSON, in km)
            $table->json('max_daily_distance_per_mode')->nullable()->after('max_daily_tracks');

            // Massimo guadagno a persona in EUR (solo informativo)
            $table->decimal('max_earning_per_person', 10, 2)->nullable()->after('max_daily_distance_per_mode');

            // Indici
            $table->index('reward_mode');
            $table->index('extension_type');
            $table->index('competition_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('competitions', function (Blueprint $table) {
            $table->dropIndex(['reward_mode']);
            $table->dropIndex(['extension_type']);
            $table->dropIndex(['competition_type']);

            $table->dropColumn([
                'extra_document',
                'extension_type',
                'competition_type',
                'reward_mode',
                'questionnaire_url',
                'age_range',
                'scoring_type',
                'credits_to_euro',
                'credits_per_mode',
                'max_distance_per_mode',
                'max_daily_distance_per_mode',
                'max_earning_per_person',
            ]);
        });
    }
};
