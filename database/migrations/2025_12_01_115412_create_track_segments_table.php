<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('track_segments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('track_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('sequence')->default(0); // Ordine del segmento nella traccia

            // Modalita trasporto per questo segmento
            $table->string('transport_mode'); // TransportMode enum

            // Stato validazione segmento
            $table->string('status')->default('pending'); // valid, invalid, pending

            // Dati temporali
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->unsignedInteger('duration_seconds')->nullable();

            // Coordinate inizio/fine segmento
            $table->decimal('start_latitude', 10, 7)->nullable();
            $table->decimal('start_longitude', 10, 7)->nullable();
            $table->decimal('end_latitude', 10, 7)->nullable();
            $table->decimal('end_longitude', 10, 7)->nullable();

            // Metriche
            $table->unsignedInteger('distance_meters')->default(0);
            $table->unsignedInteger('points_count')->default(0);
            $table->decimal('avg_speed_ms', 8, 4)->nullable();
            $table->decimal('max_speed_ms', 8, 4)->nullable();
            $table->decimal('avg_accuracy_meters', 8, 2)->nullable();

            // Crediti per questo segmento
            $table->decimal('credits_earned', 10, 4)->default(0);
            $table->boolean('generates_credits')->default(true);

            // Emissioni risparmiate per questo segmento
            $table->decimal('co2_saved_grams', 10, 2)->default(0);
            $table->decimal('calories_burned', 8, 2)->default(0);

            // Validazione
            $table->text('rejection_reason')->nullable();
            $table->json('validation_details')->nullable(); // Dettagli controlli effettuati

            // Punti GPS del segmento (opzionale, per visualizzazione)
            $table->json('polyline')->nullable(); // Array di [lat, lng] o encoded polyline

            $table->timestamps();

            // Indice per ordinamento
            $table->index(['track_id', 'sequence']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('track_segments');
    }
};
