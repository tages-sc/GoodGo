<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('track_segments', function (Blueprint $table) {
            // Velocità massima calcolata a pezzi di 500m (piedi) / 800m (bici)
            // come da Algoritmo.pdf: "Dividere il tracciato in pezzi di N metri,
            // calcolare la velocità tra l'inizio e la fine"
            $table->decimal('max_chunk_speed_kmh', 8, 2)->nullable()->after('max_speed_ms');
            // Dettaglio dei pezzi analizzati (per debug/visualizzazione)
            $table->json('speed_chunks')->nullable()->after('max_chunk_speed_kmh');
        });
    }

    public function down(): void
    {
        Schema::table('track_segments', function (Blueprint $table) {
            $table->dropColumn(['max_chunk_speed_kmh', 'speed_chunks']);
        });
    }
};
