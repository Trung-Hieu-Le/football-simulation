<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            // Xóa các cột cũ
            $table->dropColumn(['aggressive', 'penalty']);
            
            // Thêm các cột mới
            $table->integer('pass')->default(0)->after('control');
            $table->integer('speed')->default(0)->after('pass');
            $table->integer('mental')->default(0)->after('speed');
            $table->integer('discipline')->default(0)->after('mental');
        });
    }

    public function down(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            // Khôi phục các cột cũ
            $table->integer('aggressive')->default(0);
            $table->integer('penalty')->default(0);
            
            // Xóa các cột mới
            $table->dropColumn(['pass', 'speed', 'mental', 'discipline']);
        });
    }
};