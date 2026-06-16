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
        Schema::create('competition_user', function (Blueprint $table) {
            $table->id();

            $table->foreignId('competition_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Stato iscrizione
            $table->enum('status', ['pending', 'approved', 'rejected', 'withdrawn'])->default('pending');

            // Date
            $table->timestamp('registered_at')->useCurrent();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('withdrawn_at')->nullable();

            // Approvazione
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('rejection_reason')->nullable();

            // Statistiche utente nella gara (cache)
            $table->unsignedInteger('tracks_count')->default(0);
            $table->decimal('total_distance_km', 10, 2)->default(0);
            $table->decimal('total_credits', 10, 2)->default(0);
            $table->decimal('total_co2_saved_kg', 10, 2)->default(0);
            $table->unsignedInteger('rank')->nullable(); // Posizione in classifica

            $table->timestamps();

            // Unique constraint: un utente può iscriversi una sola volta a una gara
            $table->unique(['competition_id', 'user_id']);

            // Indici
            $table->index('status');
            $table->index('registered_at');
            $table->index('total_credits');
            $table->index('rank');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('competition_user');
    }
};
