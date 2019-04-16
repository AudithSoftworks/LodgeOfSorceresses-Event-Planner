<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLastDpsAmountFieldToCharactersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('characters', static function (Blueprint $table) {
            $table->unsignedInteger('last_submitted_dps_amount')->after('approved_for_endgame_t2')->nullable();
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
            $table->dropColumn('last_submitted_dps_amount');
        });
    }
}
