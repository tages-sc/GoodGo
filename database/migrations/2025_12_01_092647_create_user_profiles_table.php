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
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Dati profilo base
            $table->string('username')->nullable()->unique();
            $table->string('photo')->nullable();
            $table->date('birth_date')->nullable();

            // Dati di contatto aggiuntivi
            $table->string('phone')->nullable();

            // Indirizzo
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('province')->nullable();
            $table->string('postal_code')->nullable();

            // Campi extra dinamici (JSON)
            $table->json('extra_fields')->nullable();

            $table->timestamps();

            // Indici
            $table->index('username');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_profiles');
    }
};
