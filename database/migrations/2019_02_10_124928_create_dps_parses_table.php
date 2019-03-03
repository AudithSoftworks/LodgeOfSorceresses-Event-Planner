<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDpsParsesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dps_parses', function (Blueprint $table) {
            $table->engine = 'InnoDb';
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('character_id');
            $table->string('sets');
            $table->unsignedInteger('dps_amount');
            $table->string('parse_file_hash', 64);
            $table->string('superstar_file_hash', 64)->nullable();
            $table->unsignedInteger('approved_by')->nullable();
            $table->boolean('approved_for_midgame')->default(false);
            $table->boolean('approved_for_endgame_t0')->default(false);
            $table->boolean('approved_for_endgame_t1')->default(false);
            $table->boolean('approved_for_endgame_t2')->default(false);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('character_id')->references('id')->on('characters')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('parse_file_hash')->references('hash')->on('files')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('superstar_file_hash')->references('hash')->on('files')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('approved_by')->references('id')->on('users')->onUpdate('cascade')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('dps_parses', function (Blueprint $table) {
            $table->dropForeign('dps_parses_user_id_foreign');
            $table->dropForeign('dps_parses_character_id_foreign');
            $table->dropForeign('dps_parses_parse_file_hash_foreign');
            $table->dropForeign('dps_parses_superstar_file_hash_foreign');
            $table->dropForeign('dps_parses_approved_by_foreign');
        });
        Schema::dropIfExists('dps_parses');
    }
}
