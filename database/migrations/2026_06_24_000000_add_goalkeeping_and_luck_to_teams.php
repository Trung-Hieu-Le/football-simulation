<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add `goalkeeping` and `luck` columns to teams for new 10-stat model.
     */
    public function up()
    {
        Schema::table('teams', function (Blueprint $table) {
            if (!Schema::hasColumn('teams', 'goalkeeping')) {
                $table->integer('goalkeeping')->default(50)->after('stamina');
            }
            if (!Schema::hasColumn('teams', 'luck')) {
                $table->integer('luck')->default(50)->after('discipline');
            }
        });
    }

    public function down()
    {
        Schema::table('teams', function (Blueprint $table) {
            if (Schema::hasColumn('teams', 'goalkeeping')) {
                $table->dropColumn('goalkeeping');
            }
            if (Schema::hasColumn('teams', 'luck')) {
                $table->dropColumn('luck');
            }
        });
    }
};
