<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    Schema::create('teams', function (Blueprint $table) {
        $table->id();
        $table->string('name', 255);
        $table->string('color_1', 10)->nullable();
        $table->string('color_2', 10)->nullable();
        $table->string('color_3', 10)->nullable();
        $table->integer('attack')->default(0);
        $table->integer('defense')->default(0);
        $table->integer('control')->default(0);
        $table->integer('stamina')->default(0);
        $table->integer('aggressive')->default(0);
        $table->integer('penalty')->default(0);
        $table->integer('form')->default(0);
        $table->unsignedBigInteger('region'); // Liên kết đến bảng regions
        $table->timestamps();

    });
}

public function down()
{
    Schema::dropIfExists('teams');
}

};
