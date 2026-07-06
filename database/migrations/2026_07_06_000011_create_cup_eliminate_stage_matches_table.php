<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cup_eliminate_stage_matches', function (Blueprint $table) {
            $table->id();
            $table->integer('season_id');
            $table->string('round', 45)->nullable();
            $table->string('branch', 45)->nullable();
            $table->integer('team1_id')->nullable();
            $table->integer('team2_id')->nullable();
            $table->tinyInteger('team1_score')->nullable();
            $table->tinyInteger('team2_score')->nullable();
            $table->integer('team1_possession')->default(50);
            $table->integer('team2_possession')->default(50);
            $table->integer('team1_foul')->default(0);
            $table->integer('team2_foul')->default(0);
            $table->integer('winner_id')->nullable();
            $table->timestamps();
            
            $table->index('season_id');
            $table->index('winner_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cup_eliminate_stage_matches');
    }
};
