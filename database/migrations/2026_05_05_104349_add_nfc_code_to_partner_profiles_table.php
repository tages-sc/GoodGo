<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('partner_profiles', function (Blueprint $table) {
            $table->string('nfc_code', 50)->nullable()->after('company_postal_code');
            $table->index('nfc_code');
        });
    }

    public function down(): void
    {
        Schema::table('partner_profiles', function (Blueprint $table) {
            $table->dropIndex(['nfc_code']);
            $table->dropColumn('nfc_code');
        });
    }
};
