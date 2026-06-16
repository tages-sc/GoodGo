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
        Schema::create('ente_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Dati base
            $table->string('tipologia')->nullable(); // Tipo di ente (comune, associazione, azienda, etc.)
            $table->string('location')->nullable(); // Località/sede
            $table->text('descrizione')->nullable();

            // Immagini
            $table->string('logo')->nullable();
            $table->string('banner')->nullable();

            // Iscrizioni
            $table->boolean('iscrizione_moderata')->default(false);

            // Links e social
            $table->string('website')->nullable();
            $table->string('instagram_url')->nullable();
            $table->string('linkedin_url')->nullable();
            $table->string('twitter_url')->nullable();
            $table->string('facebook_url')->nullable();

            // Personalizzazione
            $table->string('colore', 7)->nullable(); // Colore esadecimale (#RRGGBB)

            // Flag default (solo GoodGo dovrebbe avere questo a true)
            $table->boolean('is_default')->default(false);

            $table->timestamps();

            // Indici
            $table->index('tipologia');
            $table->index('is_default');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ente_profiles');
    }
};
