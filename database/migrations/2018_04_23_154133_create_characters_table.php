<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCharactersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('characters', static function (Blueprint $table) {
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
    public function down(): void
    {
        Schema::table('characters', static function (Blueprint $table) {
            $table->dropForeign('characters_user_id_foreign');
        });
        Schema::drop('characters');
    }
}
