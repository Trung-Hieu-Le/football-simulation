<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cup_group_teams', function (Blueprint $table) {
            $table->id();
            $table->integer('season_id');
            $table->string('group', 255); // A, B, C, D, E, F, G, H
            $table->string('team_ids', 1015)->nullable();
            $table->timestamps();
            
            $table->index('season_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cup_group_teams');
    }
};
