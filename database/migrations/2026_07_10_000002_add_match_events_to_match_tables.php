<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('league_matches', function (Blueprint $table) {
            $table->json('match_events')->nullable()->after('team2_foul');
        });

        Schema::table('cup_group_stage_matches', function (Blueprint $table) {
            $table->json('match_events')->nullable()->after('team2_foul');
        });

        Schema::table('cup_eliminate_stage_matches', function (Blueprint $table) {
            $table->json('match_events')->nullable()->after('team2_foul');
        });
    }

    public function down(): void
    {
        Schema::table('league_matches', function (Blueprint $table) {
            $table->dropColumn('match_events');
        });

        Schema::table('cup_group_stage_matches', function (Blueprint $table) {
            $table->dropColumn('match_events');
        });

        Schema::table('cup_eliminate_stage_matches', function (Blueprint $table) {
            $table->dropColumn('match_events');
        });
    }
};
