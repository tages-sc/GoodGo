<?php

use App\Enums\CompetitionStatus;
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
        Schema::create('competitions', function (Blueprint $table) {
            $table->id();

            // Dati base
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->text('rules')->nullable(); // Regolamento gara
            $table->text('prizes')->nullable(); // Premi

            // Immagini
            $table->string('image')->nullable(); // Immagine principale
            $table->string('banner')->nullable(); // Banner

            // Ente organizzatore (riferimento a un utente con type=ente o organizer)
            $table->foreignId('ente_id')->constrained('users')->cascadeOnDelete();

            // Organizzatore (utente con ruolo organizer)
            $table->foreignId('organizer_id')->nullable()->constrained('users')->nullOnDelete();

            // Date gara
            $table->date('start_date');
            $table->date('end_date');

            // Date iscrizione
            $table->dateTime('registration_start')->nullable();
            $table->dateTime('registration_end')->nullable();

            // Stato
            $table->string('status')->default(CompetitionStatus::DRAFT->value);

            // Impostazioni
            $table->boolean('is_public')->default(true); // Visibile pubblicamente
            $table->unsignedInteger('max_participants')->nullable(); // Limite iscritti (null = illimitato)
            $table->boolean('moderated_subscription')->default(false); // Iscrizioni moderate

            // Territorio valido per tracce (null = tutto il territorio)
            $table->json('allowed_municipality_ids')->nullable();
            $table->json('allowed_province_ids')->nullable();
            $table->json('allowed_region_ids')->nullable();

            // Impostazioni tracce
            $table->unsignedInteger('min_track_distance')->nullable(); // Distanza minima traccia (metri)
            $table->unsignedInteger('max_track_distance')->nullable(); // Distanza massima traccia (metri)
            $table->unsignedInteger('max_daily_tracks')->default(1); // Max tracce al giorno per utente
            $table->json('allowed_transport_modes')->nullable(); // Modi di trasporto ammessi

            // Formula crediti (può sovrascrivere quella di default)
            $table->decimal('credits_per_km', 8, 4)->nullable();
            $table->decimal('credits_multiplier', 5, 2)->default(1.00);

            // Statistiche cache
            $table->unsignedInteger('participants_count')->default(0);
            $table->unsignedInteger('tracks_count')->default(0);
            $table->decimal('total_distance_km', 12, 2)->default(0);
            $table->decimal('total_co2_saved_kg', 12, 2)->default(0);

            // Clonazione
            $table->foreignId('cloned_from_id')->nullable()->constrained('competitions')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            // Indici
            $table->index('name');
            $table->index('status');
            $table->index('start_date');
            $table->index('end_date');
            $table->index('is_public');
            $table->index(['start_date', 'end_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('competitions');
    }
};
