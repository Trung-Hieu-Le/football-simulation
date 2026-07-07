<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('regions', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->string('name', 45);
            $table->string('shortname', 45);
            $table->string('description', 45)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('regions');
    }
};
