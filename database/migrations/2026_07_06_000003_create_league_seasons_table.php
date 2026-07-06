<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('league_seasons', function (Blueprint $table) {
            $table->id();
            $table->integer('season');
            $table->integer('teams_count');
            $table->string('meta', 45)->default('attack');
            $table->timestamp('created_at')->nullable();
            
            $table->index('season');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('league_seasons');
    }
};
