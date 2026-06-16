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
        Schema::create('municipalities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('province_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('postal_code', 5)->nullable(); // CAP
            $table->string('istat_code')->nullable(); // Codice ISTAT
            $table->string('cadastral_code', 4)->nullable(); // Codice catastale

            // Coordinate per reverse geocoding
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            // OpenStreetMap ID per reverse geocoding
            $table->unsignedBigInteger('osm_id')->nullable();

            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('name');
            $table->index('postal_code');
            $table->index('istat_code');
            $table->index('is_active');
            $table->index(['latitude', 'longitude']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('municipalities');
    }
};
