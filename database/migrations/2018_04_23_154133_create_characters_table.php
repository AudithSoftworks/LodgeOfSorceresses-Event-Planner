<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCharactersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('characters', function (Blueprint $table) {
            $table->engine = 'InnoDb';
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->string('name');
            $table->integer('class');
            $table->integer('role');
            $table->string('sets');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('characters', function (Blueprint $table) {
            $table->dropForeign('characters_user_id_foreign');
        });
        Schema::drop('characters');
    }
}
