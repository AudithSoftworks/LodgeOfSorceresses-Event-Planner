<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTeamsCharactersTable extends Migration
{
    public function up(): void
    {
        Schema::create('teams_characters', static function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedMediumInteger('team_id');
            $table->unsignedInteger('character_id');
            $table->boolean('status')->default(false);
            $table->boolean('accepted_terms')->default(false);
            $table->timestamps();

            $table->foreign('team_id')->references('id')->on('teams')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('character_id')->references('id')->on('characters')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('teams_characters', static function (Blueprint $table) {
            $table->dropForeign('teams_characters_team_id_foreign');
            $table->dropForeign('teams_characters_character_id_foreign');
        });
        Schema::drop('teams_characters');
    }
}
