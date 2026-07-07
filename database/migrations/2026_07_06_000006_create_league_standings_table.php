<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('league_standings', function (Blueprint $table) {
            $table->id();
            $table->integer('team_id');
            $table->integer('season_id')->default(0);
            $table->string('division', 45)->nullable();
            $table->integer('match_played')->default(0);
            $table->integer('goal_scored')->default(0);
            $table->integer('goal_conceded')->default(0);
            $table->integer('goal_difference')->default(0);
            $table->double('average_possession')->default(50);
            $table->integer('foul')->default(0);
            $table->integer('points')->default(0);
            $table->integer('win')->default(0);
            $table->integer('draw')->default(0);
            $table->integer('lose')->default(0);
            $table->timestamps();
            
            $table->index(['season_id', 'team_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('league_standings');
    }
};
