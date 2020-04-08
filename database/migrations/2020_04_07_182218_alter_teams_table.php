<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTeamsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('teams', static function (Blueprint $table) {
            $table->string('discord_lobby_channel_id')->nullable()->after('discord_id');
            $table->string('discord_rant_channel_id')->nullable()->after('discord_lobby_channel_id');
            $table->renameColumn('discord_id', 'discord_role_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('teams', static function (Blueprint $table) {
            $table->renameColumn('discord_role_id', 'discord_id');
            $table->dropColumn(['discord_lobby_channel_id', 'discord_rant_channel_id']);
        });
    }
}
