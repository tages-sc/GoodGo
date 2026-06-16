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
        Schema::create('invitation_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ente_id')->constrained('users')->cascadeOnDelete();
            $table->string('name', 255);
            $table->string('code', 6)->unique();
            $table->date('expires_at');
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('max_uses')->default(0); // 0 = illimitato
            $table->unsignedInteger('uses_count')->default(0);
            $table->timestamps();

            $table->index('ente_id');
            $table->index('is_active');
        });

        // Aggiunge invitation_code_id alla pivot ente_user
        Schema::table('ente_user', function (Blueprint $table) {
            $table->foreignId('invitation_code_id')->nullable()->after('notes')
                ->constrained('invitation_codes')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ente_user', function (Blueprint $table) {
            $table->dropForeign(['invitation_code_id']);
            $table->dropColumn('invitation_code_id');
        });

        Schema::dropIfExists('invitation_codes');
    }
};
