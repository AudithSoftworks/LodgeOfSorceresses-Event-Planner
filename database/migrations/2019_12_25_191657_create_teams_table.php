<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTeamsTable extends Migration
{
    public function up(): void
    {
        Schema::create('teams', static function (Blueprint $table) {
            $table->mediumIncrements('id');
            $table->string('name');
            $table->string('icon')->nullable();
            $table->enum('tier', [1, 2, 3, 4]);
            $table->unsignedBigInteger('discord_id');
            $table->unsignedInteger('led_by');
            $table->unsignedInteger('created_by');
            $table->timestamps();

            $table->foreign('led_by')->references('id')->on('users')->onUpdate('cascade')->onDelete('restrict');
            $table->foreign('created_by')->references('id')->on('users')->onUpdate('cascade')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::table('teams', static function (Blueprint $table) {
            $table->dropForeign('teams_led_by_foreign');
            $table->dropForeign('teams_created_by_foreign');
        });
        Schema::drop('teams');
    }
}
