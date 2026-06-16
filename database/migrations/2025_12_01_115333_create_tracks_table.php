<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tracks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('competition_id')->nullable()->constrained()->nullOnDelete();
            $table->string('session_id')->index(); // ID sessione dal dispositivo

            // Stato traccia
            $table->string('status')->default('pending')->index(); // TrackStatus enum

            // Dati temporali
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->unsignedInteger('duration_seconds')->nullable(); // Durata totale in secondi

            // Dati geografici riassuntivi
            $table->decimal('start_latitude', 10, 7)->nullable();
            $table->decimal('start_longitude', 10, 7)->nullable();
            $table->decimal('end_latitude', 10, 7)->nullable();
            $table->decimal('end_longitude', 10, 7)->nullable();

            // Metriche distanza
            $table->unsignedInteger('total_distance_meters')->default(0);
            $table->unsignedInteger('valid_distance_meters')->default(0); // Distanza accreditata

            // Flag multimodale
            $table->boolean('is_multimodal')->default(false);
            $table->string('primary_transport_mode')->nullable(); // TransportMode principale

            // Crediti
            $table->decimal('credits_earned', 10, 4)->default(0);
            $table->decimal('credits_per_km_applied', 10, 4)->nullable();

            // Emissioni risparmiate (grammi)
            $table->decimal('co2_saved_grams', 10, 2)->default(0);
            $table->decimal('so2_saved_mg', 10, 4)->default(0);
            $table->decimal('nox_saved_grams', 10, 4)->default(0);
            $table->decimal('co_saved_grams', 10, 4)->default(0);
            $table->decimal('pm10_saved_grams', 10, 6)->default(0);

            // Calorie bruciate
            $table->decimal('calories_burned', 8, 2)->default(0);

            // File originale
            $table->string('original_file_path')->nullable(); // Path al file ZIP/TXT caricato
            $table->string('original_file_hash')->nullable(); // Hash per evitare duplicati
            $table->unsignedInteger('points_count')->default(0); // Numero punti GPS

            // Qualita dati
            $table->decimal('avg_accuracy_meters', 8, 2)->nullable();
            $table->decimal('avg_speed_ms', 8, 4)->nullable(); // Velocita media m/s

            // Validazione
            $table->timestamp('validated_at')->nullable();
            $table->foreignId('validated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('rejection_reason')->nullable();
            $table->json('validation_warnings')->nullable(); // Array di warning non bloccanti

            // Metadati dispositivo
            $table->string('device_platform')->nullable(); // ios/android
            $table->json('device_info')->nullable(); // Info aggiuntive dispositivo

            $table->timestamps();

            // Indici per query frequenti
            $table->index(['user_id', 'status']);
            $table->index(['competition_id', 'status']);
            $table->index(['started_at', 'ended_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tracks');
    }
};
