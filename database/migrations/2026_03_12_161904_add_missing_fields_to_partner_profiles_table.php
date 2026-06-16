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
        Schema::table('partner_profiles', function (Blueprint $table) {
            // Indirizzo azienda (separato da sede legale/operativa)
            $table->string('company_address')->nullable()->after('company_type');
            $table->string('company_city')->nullable()->after('company_address');
            $table->string('company_province')->nullable()->after('company_city');
            $table->string('company_postal_code')->nullable()->after('company_province');

            // Rappresentante legale - campi mancanti
            $table->string('legal_rep_birth_place')->nullable()->after('legal_rep_email');
            $table->date('legal_rep_birth_date')->nullable()->after('legal_rep_birth_place');
            $table->string('legal_rep_address')->nullable()->after('legal_rep_birth_date');
            $table->string('legal_rep_city')->nullable()->after('legal_rep_address');
            $table->string('legal_rep_province')->nullable()->after('legal_rep_city');
            $table->string('legal_rep_postal_code')->nullable()->after('legal_rep_province');

            // Operatore 1 - codice fiscale mancante
            $table->string('operator_fiscal_code')->nullable()->after('operator_surname');

            // Operatore 2
            $table->string('operator2_name')->nullable()->after('operator_email');
            $table->string('operator2_surname')->nullable()->after('operator2_name');
            $table->string('operator2_fiscal_code')->nullable()->after('operator2_surname');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('partner_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'company_address',
                'company_city',
                'company_province',
                'company_postal_code',
                'legal_rep_birth_place',
                'legal_rep_birth_date',
                'legal_rep_address',
                'legal_rep_city',
                'legal_rep_province',
                'legal_rep_postal_code',
                'operator_fiscal_code',
                'operator2_name',
                'operator2_surname',
                'operator2_fiscal_code',
            ]);
        });
    }
};
