<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('badges', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description');
            $table->string('category'); // registrazione, tracce, modalita, distanza, emissioni, salute, economia
            $table->unsignedTinyInteger('stars')->default(0); // 0 = nessuna stella, 1-3 per livelli
            $table->string('icon')->nullable(); // path immagine
            $table->string('threshold_type')->nullable(); // tipo di soglia: count, distance_km, co2_kg, calories, money_euro
            $table->decimal('threshold_value', 10, 2)->nullable(); // valore soglia
            $table->json('extra_conditions')->nullable(); // condizioni aggiuntive JSON
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('user_badges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('badge_id')->constrained()->cascadeOnDelete();
            $table->timestamp('earned_at');
            $table->timestamps();

            $table->unique(['user_id', 'badge_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_badges');
        Schema::dropIfExists('badges');
    }
};
