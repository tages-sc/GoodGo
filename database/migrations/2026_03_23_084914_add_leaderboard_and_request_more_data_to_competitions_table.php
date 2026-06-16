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
        Schema::table('competitions', function (Blueprint $table) {
            $table->string('leaderboard_type', 20)->default('km')->after('scoring_type'); // km, co2, gekoin
            $table->boolean('request_more_data')->default(false)->after('leaderboard_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('competitions', function (Blueprint $table) {
            $table->dropColumn(['leaderboard_type', 'request_more_data']);
        });
    }
};
