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
        Schema::create('ente_user', function (Blueprint $table) {
            $table->id();

            // Ente (utente con type=ente o organizer)
            $table->foreignId('ente_id')->constrained('users')->cascadeOnDelete();

            // Utente iscritto
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Stato iscrizione
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');

            // Timestamp gestione
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();

            $table->timestamps();

            // Indici
            $table->unique(['ente_id', 'user_id']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ente_user');
    }
};
