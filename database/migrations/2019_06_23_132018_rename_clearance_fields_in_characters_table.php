<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameClearanceFieldsInCharactersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('characters', static function (Blueprint $table) {
            $table->renameColumn('approved_for_midgame', 'approved_for_t1');
            $table->renameColumn('approved_for_endgame_t0', 'approved_for_t2');
            $table->renameColumn('approved_for_endgame_t1', 'approved_for_t3');
            $table->renameColumn('approved_for_endgame_t2', 'approved_for_t4');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('characters', static function (Blueprint $table) {
            $table->renameColumn('approved_for_t1', 'approved_for_midgame');
            $table->renameColumn('approved_for_t2', 'approved_for_endgame_t0');
            $table->renameColumn('approved_for_t3', 'approved_for_endgame_t1');
            $table->renameColumn('approved_for_t4', 'approved_for_endgame_t2');
        });
    }
}
