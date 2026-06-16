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
        Schema::create('partner_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Dati aziendali
            $table->string('company_name');
            $table->string('vat_number')->nullable(); // Partita IVA
            $table->string('fiscal_code')->nullable(); // Codice fiscale
            $table->string('company_type')->nullable(); // Tipo società (SRL, SPA, etc.)

            // Sede legale
            $table->string('legal_address')->nullable();
            $table->string('legal_city')->nullable();
            $table->string('legal_province')->nullable();
            $table->string('legal_postal_code')->nullable();

            // Sede operativa (se diversa)
            $table->string('operational_address')->nullable();
            $table->string('operational_city')->nullable();
            $table->string('operational_province')->nullable();
            $table->string('operational_postal_code')->nullable();

            // Contatti aziendali
            $table->string('company_phone')->nullable();
            $table->string('company_email')->nullable();
            $table->string('pec')->nullable(); // PEC
            $table->string('website')->nullable();

            // Rappresentante legale
            $table->string('legal_rep_name')->nullable();
            $table->string('legal_rep_surname')->nullable();
            $table->string('legal_rep_fiscal_code')->nullable();
            $table->string('legal_rep_phone')->nullable();
            $table->string('legal_rep_email')->nullable();

            // Dati bancari
            $table->string('iban')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('swift_bic')->nullable();

            // Operatore/referente (chi gestisce l'account)
            $table->string('operator_name')->nullable();
            $table->string('operator_surname')->nullable();
            $table->string('operator_phone')->nullable();
            $table->string('operator_email')->nullable();

            // Logo e descrizione
            $table->string('logo')->nullable();
            $table->text('description')->nullable();

            // Stato verifica
            $table->boolean('is_verified')->default(false);
            $table->timestamp('verified_at')->nullable();

            $table->timestamps();

            // Indici
            $table->index('vat_number');
            $table->index('company_name');
            $table->index('is_verified');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('partner_profiles');
    }
};
