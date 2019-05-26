<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCharactersContentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('characters_content', static function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('character_id');
            $table->unsignedSmallInteger('content_id');
            $table->timestamps();

            $table->foreign('content_id')->references('id')->on('content')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('character_id')->references('id')->on('characters')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('characters_content', static function (Blueprint $table) {
            $table->dropForeign('characters_content_content_id_foreign');
            $table->dropForeign('characters_content_character_id_foreign');
        });
        Schema::drop('characters_content');
    }
}
