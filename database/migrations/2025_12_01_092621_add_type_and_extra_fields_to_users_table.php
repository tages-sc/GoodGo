<?php

use App\Enums\UserType;
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
        Schema::table('users', function (Blueprint $table) {
            // Tipo utente
            $table->string('type')->default(UserType::USER->value)->after('email');

            // Piattaforma di registrazione (app mobile o web)
            $table->enum('platform', ['ios', 'android', 'web'])->nullable()->after('type');

            // Crediti utente
            $table->decimal('credits', 10, 2)->default(0)->after('platform');

            // Accettazione Privacy Policy
            $table->timestamp('privacy_accepted_at')->nullable()->after('email_verified_at');
            $table->unsignedBigInteger('privacy_version_id')->nullable()->after('privacy_accepted_at');

            // Accettazione Terms of Service
            $table->timestamp('terms_accepted_at')->nullable()->after('privacy_version_id');
            $table->unsignedBigInteger('terms_version_id')->nullable()->after('terms_accepted_at');

            // Ente corrente selezionato (riferimento a un utente con type=ente)
            $table->foreignId('current_ente_id')->nullable()->after('terms_version_id');

            // Indici
            $table->index('type');
            $table->index('platform');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['type']);
            $table->dropIndex(['platform']);

            $table->dropColumn([
                'type',
                'platform',
                'credits',
                'privacy_accepted_at',
                'privacy_version_id',
                'terms_accepted_at',
                'terms_version_id',
                'current_ente_id',
            ]);
        });
    }
};
