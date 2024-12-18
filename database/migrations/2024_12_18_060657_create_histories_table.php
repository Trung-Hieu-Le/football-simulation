<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('team_id');
            $table->unsignedBigInteger('season_id');
            $table->string('group', 45)->nullable();
            $table->integer('match_played')->default(0);
            $table->integer('goal_scored')->default(0);
            $table->integer('goal_conceded')->default(0);
            $table->integer('goal_difference')->default(0);
            $table->integer('position')->default(0);
            $table->string('tier', 45);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('histories');
    }
};
