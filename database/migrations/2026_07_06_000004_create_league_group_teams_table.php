<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('league_group_teams', function (Blueprint $table) {
            $table->id();
            $table->integer('season_id');
            $table->string('group', 255); // division1, division2, division3
            $table->string('team_ids', 1015);
            $table->timestamp('created_at')->nullable();
            
            $table->index('season_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('league_group_teams');
    }
};
