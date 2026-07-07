<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('league_matches', function (Blueprint $table) {
            $table->id();
            $table->integer('season_id');
            $table->string('division', 45); // division1, division2, division3
            $table->integer('round')->nullable();
            $table->integer('team1_id')->default(0);
            $table->integer('team2_id')->default(0);
            $table->integer('team1_score')->nullable();
            $table->integer('team2_score')->nullable();
            $table->integer('team1_possession')->default(50);
            $table->integer('team2_possession')->default(50);
            $table->integer('team1_foul')->default(0);
            $table->integer('team2_foul')->default(0);
            $table->timestamps();
            
            $table->index(['season_id', 'division']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('league_matches');
    }
};
