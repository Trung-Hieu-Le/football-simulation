
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Update team stats to new 8-attribute model:
     * attack, creative, control, pace, defense, mental, discipline, stamina.
     */
    public function up()
    {
        Schema::table('teams', function (Blueprint $table) {
            if (!Schema::hasColumn('teams', 'creative')) {
                $table->integer('creative')->default(55)->after('attack');
            }

            if (!Schema::hasColumn('teams', 'pace')) {
                // Place pace near control for readability
                if (Schema::hasColumn('teams', 'control')) {
                    $table->integer('pace')->default(55)->after('control');
                } else {
                    $table->integer('pace')->default(55);
                }
            }

            // Old stats to be removed from the game model
            if (Schema::hasColumn('teams', 'pass')) {
                $table->dropColumn('pass');
            }
            if (Schema::hasColumn('teams', 'speed')) {
                $table->dropColumn('speed');
            }
            if (Schema::hasColumn('teams', 'aggressive')) {
                $table->dropColumn('aggressive');
            }
            if (Schema::hasColumn('teams', 'penalty')) {
                $table->dropColumn('penalty');
            }
        });
    }

    public function down()
    {
        Schema::table('teams', function (Blueprint $table) {
            if (Schema::hasColumn('teams', 'creative')) {
                $table->dropColumn('creative');
            }
            if (Schema::hasColumn('teams', 'pace')) {
                $table->dropColumn('pace');
            }

            // Restore legacy columns (without data)
            if (!Schema::hasColumn('teams', 'pass')) {
                $table->integer('pass')->default(55);
            }
            if (!Schema::hasColumn('teams', 'speed')) {
                $table->integer('speed')->default(55);
            }
            if (!Schema::hasColumn('teams', 'aggressive')) {
                $table->integer('aggressive')->default(0);
            }
            if (!Schema::hasColumn('teams', 'penalty')) {
                $table->integer('penalty')->default(0);
            }
        });
    }
};


