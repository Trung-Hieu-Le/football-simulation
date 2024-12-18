<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('team_groups', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('season_id');
            $table->string('group_name', 45);
            $table->string('team_ids', 1015); // Lưu danh sách ID dưới dạng chuỗi JSON
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('team_groups');
    }
};
