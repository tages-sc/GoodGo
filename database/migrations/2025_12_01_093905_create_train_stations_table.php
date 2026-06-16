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
        Schema::create('train_stations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->nullable(); // Codice stazione RFI

            // Coordinate
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);

            // Riferimento geografico
            $table->foreignId('municipality_id')->nullable()->constrained()->nullOnDelete();

            // OpenStreetMap
            $table->unsignedBigInteger('osm_id')->nullable();

            // Tipo stazione
            $table->enum('type', ['national', 'regional', 'local'])->default('regional');

            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('name');
            $table->index('code');
            $table->index(['latitude', 'longitude']);
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('train_stations');
    }
};
