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
        Schema::create('bus_stops', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->nullable(); // Codice fermata

            // Coordinate
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);

            // Riferimento geografico
            $table->foreignId('municipality_id')->nullable()->constrained()->nullOnDelete();

            // Linee che passano per la fermata
            $table->string('lines')->nullable(); // Es: "1, 5, LAM Rossa"

            // Operatore TPL
            $table->string('operator')->nullable(); // Es: CTT Nord, ATL

            // OpenStreetMap
            $table->unsignedBigInteger('osm_id')->nullable();

            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('name');
            $table->index('code');
            $table->index(['latitude', 'longitude']);
            $table->index('is_active');
            $table->index('municipality_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bus_stops');
    }
};
