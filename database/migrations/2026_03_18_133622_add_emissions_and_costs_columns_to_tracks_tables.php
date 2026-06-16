<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Emissioni effettive e costi trasporto per tracks
        Schema::table('tracks', function (Blueprint $table) {
            // Emissioni effettive (quanto emesso con la modalità usata)
            $table->decimal('co2_emitted_grams', 10, 2)->default(0)->after('calories_burned');
            $table->decimal('so2_emitted_mg', 10, 4)->default(0)->after('co2_emitted_grams');
            $table->decimal('nox_emitted_grams', 10, 4)->default(0)->after('so2_emitted_mg');
            $table->decimal('co_emitted_grams', 10, 4)->default(0)->after('nox_emitted_grams');
            $table->decimal('pm10_emitted_grams', 10, 6)->default(0)->after('co_emitted_grams');

            // Costi di trasporto equivalenti (come da documentazione Algoritmo.pdf)
            $table->decimal('cost_fuel_euros', 8, 4)->default(0)->after('pm10_emitted_grams');
            $table->decimal('cost_depreciation_euros', 8, 4)->default(0)->after('cost_fuel_euros');
            $table->decimal('cost_operation_euros', 8, 4)->default(0)->after('cost_depreciation_euros');
            $table->decimal('cost_time_euros', 8, 4)->default(0)->after('cost_operation_euros');
            $table->decimal('cost_total_euros', 8, 4)->default(0)->after('cost_time_euros');
        });

        // Emissioni effettive, risparmiate complete e costi per track_segments
        Schema::table('track_segments', function (Blueprint $table) {
            // Emissioni risparmiate aggiuntive (co2_saved_grams e calories_burned già esistono)
            $table->decimal('so2_saved_mg', 10, 4)->default(0)->after('calories_burned');
            $table->decimal('nox_saved_grams', 10, 4)->default(0)->after('so2_saved_mg');
            $table->decimal('co_saved_grams', 10, 4)->default(0)->after('nox_saved_grams');
            $table->decimal('pm10_saved_grams', 10, 6)->default(0)->after('co_saved_grams');

            // Emissioni effettive
            $table->decimal('co2_emitted_grams', 10, 2)->default(0)->after('pm10_saved_grams');
            $table->decimal('so2_emitted_mg', 10, 4)->default(0)->after('co2_emitted_grams');
            $table->decimal('nox_emitted_grams', 10, 4)->default(0)->after('so2_emitted_mg');
            $table->decimal('co_emitted_grams', 10, 4)->default(0)->after('nox_emitted_grams');
            $table->decimal('pm10_emitted_grams', 10, 6)->default(0)->after('co_emitted_grams');

            // Costi di trasporto equivalenti
            $table->decimal('cost_fuel_euros', 8, 4)->default(0)->after('pm10_emitted_grams');
            $table->decimal('cost_depreciation_euros', 8, 4)->default(0)->after('cost_fuel_euros');
            $table->decimal('cost_operation_euros', 8, 4)->default(0)->after('cost_depreciation_euros');
            $table->decimal('cost_time_euros', 8, 4)->default(0)->after('cost_operation_euros');
            $table->decimal('cost_total_euros', 8, 4)->default(0)->after('cost_time_euros');
        });
    }

    public function down(): void
    {
        Schema::table('tracks', function (Blueprint $table) {
            $table->dropColumn([
                'co2_emitted_grams', 'so2_emitted_mg', 'nox_emitted_grams',
                'co_emitted_grams', 'pm10_emitted_grams',
                'cost_fuel_euros', 'cost_depreciation_euros', 'cost_operation_euros',
                'cost_time_euros', 'cost_total_euros',
            ]);
        });

        Schema::table('track_segments', function (Blueprint $table) {
            $table->dropColumn([
                'so2_saved_mg', 'nox_saved_grams', 'co_saved_grams', 'pm10_saved_grams',
                'co2_emitted_grams', 'so2_emitted_mg', 'nox_emitted_grams',
                'co_emitted_grams', 'pm10_emitted_grams',
                'cost_fuel_euros', 'cost_depreciation_euros', 'cost_operation_euros',
                'cost_time_euros', 'cost_total_euros',
            ]);
        });
    }
};
