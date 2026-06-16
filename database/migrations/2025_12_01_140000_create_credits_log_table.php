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
        Schema::create('credits_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 10, 4); // Positivo = aggiunta, Negativo = sottrazione
            $table->decimal('balance_before', 10, 4); // Saldo prima dell'operazione
            $table->decimal('balance_after', 10, 4); // Saldo dopo l'operazione
            $table->string('type'); // track_validation, manual_adjustment, movement_expense, movement_refund, etc.
            $table->string('description')->nullable();
            $table->morphs('causer'); // Chi ha causato la modifica (User admin, Track, Movement, etc.)
            $table->json('metadata')->nullable(); // Dati extra (track_id, competition_id, etc.)
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('credits_log');
    }
};
