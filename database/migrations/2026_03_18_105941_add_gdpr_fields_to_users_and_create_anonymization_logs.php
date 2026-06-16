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
        // Aggiungi campo deletion_requested_at agli utenti
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('deletion_requested_at')->nullable()->after('terms_version_id');
        });

        // Rendi user_id nullable nelle tabelle che manteniamo per statistiche
        Schema::table('tracks', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->change();
        });

        Schema::table('movements', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->change();
            $table->foreignId('partner_id')->nullable()->change();
        });

        Schema::table('credits_log', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->change();
        });

        // Tabella log anonimizzazione per audit
        Schema::create('anonymization_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('original_user_id');
            $table->string('email_hash');
            $table->string('user_type');
            $table->string('action'); // self_deletion, admin_deletion
            $table->unsignedBigInteger('performed_by')->nullable(); // admin ID se cancellazione da admin
            $table->json('summary')->nullable(); // dati aggregati preservati
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('anonymization_logs');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('deletion_requested_at');
        });

        Schema::table('tracks', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable(false)->change();
        });

        Schema::table('movements', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable(false)->change();
            $table->foreignId('partner_id')->nullable(false)->change();
        });

        Schema::table('credits_log', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable(false)->change();
        });
    }
};
