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
        Schema::create('policy_versions', function (Blueprint $table) {
            $table->id();

            // Tipo: privacy o terms
            $table->string('type');

            // Versione (es. "1.0", "1.1", "2.0")
            $table->string('version');

            // Titolo
            $table->string('title');

            // Contenuto completo (HTML/Markdown)
            $table->longText('content');

            // Stato: draft o published
            $table->enum('status', ['draft', 'published'])->default('draft');

            // Data di pubblicazione
            $table->timestamp('published_at')->nullable();

            // Utente che ha creato/pubblicato
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('published_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            // Indici
            $table->index('type');
            $table->index('status');
            $table->index(['type', 'status']);
            $table->unique(['type', 'version']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('policy_versions');
    }
};
