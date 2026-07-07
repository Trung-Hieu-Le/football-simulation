<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cup_positions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cup_standing_id');
            $table->unsignedBigInteger('season_id');
            $table->integer('position')->default(0);
            $table->string('result', 45)->default('group_stage'); // CupSeasonResult enum
            $table->timestamps();
            
            $table->index('season_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cup_positions');
    }
};
