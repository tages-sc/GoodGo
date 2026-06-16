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
        Schema::create('competition_partner', function (Blueprint $table) {
            $table->id();

            $table->foreignId('competition_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // Partner (user con type=partner)

            // Stato partecipazione
            $table->enum('status', ['pending', 'approved', 'rejected', 'withdrawn'])->default('pending');

            // Date
            $table->timestamp('registered_at')->useCurrent();
            $table->timestamp('approved_at')->nullable();

            // Approvazione
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('rejection_reason')->nullable();

            // Tipo di sponsorship/partecipazione
            $table->string('sponsorship_type')->nullable(); // gold, silver, bronze, etc.
            $table->decimal('sponsorship_amount', 10, 2)->nullable();
            $table->text('notes')->nullable();

            // Visibilità
            $table->boolean('show_in_list')->default(true);
            $table->unsignedInteger('display_order')->default(0);

            $table->timestamps();

            // Unique constraint
            $table->unique(['competition_id', 'user_id']);

            // Indici
            $table->index('status');
            $table->index('sponsorship_type');
            $table->index('display_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('competition_partner');
    }
};
