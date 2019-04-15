<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddApprovalFieldsToCharactersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('characters', static function (Blueprint $table) {
            $table->boolean('approved_for_midgame')->after('sets')->default(false);
            $table->boolean('approved_for_endgame_t0')->after('approved_for_midgame')->default(false);
            $table->boolean('approved_for_endgame_t1')->after('approved_for_endgame_t0')->default(false);
            $table->boolean('approved_for_endgame_t2')->after('approved_for_endgame_t1')->default(false);
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
            $table->dropColumn('approved_for_midgame');
            $table->dropColumn('approved_for_endgame_t0');
            $table->dropColumn('approved_for_endgame_t1');
            $table->dropColumn('approved_for_endgame_t2');
        });
    }
}
