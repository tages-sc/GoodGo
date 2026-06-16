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
        Schema::create('movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // Utente che richiede
            $table->foreignId('partner_id')->nullable()->constrained('users')->nullOnDelete(); // Partner commerciale
            $table->foreignId('competition_id')->nullable()->constrained()->nullOnDelete(); // Gara di riferimento
            $table->foreignId('ente_id')->nullable()->constrained('users')->nullOnDelete(); // Ente di riferimento

            // Importi
            $table->decimal('credits_amount', 10, 4); // Crediti richiesti
            $table->decimal('euro_amount', 10, 2)->nullable(); // Controvalore in euro (se applicabile)
            $table->decimal('exchange_rate', 8, 4)->default(1.0); // Tasso di cambio crediti/euro

            // Tipo e stato
            $table->string('type'); // expense (spesa), reward (premio), refund (rimborso), adjustment (rettifica)
            $table->string('status')->default('pending'); // pending, approved, rejected, cancelled

            // Descrizione
            $table->string('description'); // Causale/descrizione del movimento
            $table->text('notes')->nullable(); // Note aggiuntive

            // Processamento
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('processed_at')->nullable();
            $table->text('rejection_reason')->nullable();

            // Riferimento al log crediti (quando approvato)
            $table->foreignId('credit_log_id')->nullable()->constrained('credits_log')->nullOnDelete();

            $table->timestamps();

            // Indici
            $table->index(['user_id', 'status']);
            $table->index(['partner_id', 'status']);
            $table->index(['competition_id', 'status']);
            $table->index(['ente_id', 'status']);
            $table->index('type');
            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movements');
    }
};
