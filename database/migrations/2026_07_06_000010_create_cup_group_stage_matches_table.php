<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cup_group_stage_matches', function (Blueprint $table) {
            $table->id();
            $table->integer('season_id');
            $table->string('group', 45)->nullable();
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
            
            $table->index('season_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cup_group_stage_matches');
    }
};
