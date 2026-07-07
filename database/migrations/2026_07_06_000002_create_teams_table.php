<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('color_1', 10)->default('000000');
            $table->string('color_2', 10)->default('000000');
            $table->string('color_3', 10)->nullable();
            
            // 10 stats
            $table->integer('attack')->default(50);
            $table->integer('defense')->default(50);
            $table->integer('control')->default(50);
            $table->integer('creative')->default(50);
            $table->integer('pace')->default(50);
            $table->integer('mental')->default(50);
            $table->integer('discipline')->default(50);
            $table->integer('luck')->default(50);
            $table->integer('stamina')->default(50);
            $table->integer('goalkeeping')->default(50);

            // ELO rating
            $table->integer('elo')->default(1000);
            
            $table->integer('region');
            $table->foreign('region')->references('id')->on('regions');
            $table->string('shirt_type', 45)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teams');
    }
};
