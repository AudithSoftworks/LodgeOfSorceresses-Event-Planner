<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateYoutubeFeedsVideosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('youtube_feeds_videos', static function (Blueprint $table) {
            $table->string('id', 24)->primary();
            $table->string('channel_id', 32);
            $table->string('title');
            $table->text('description');
            $table->string('url');
            $table->string('thumbnail');
            $table->string('discord_message_id')->nullable();
            $table->timestamps();

            $table->foreign('channel_id')->references('id')->on('youtube_feeds_channels')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('youtube_feeds_videos', static function (Blueprint $table) {
            $table->dropForeign('youtube_feeds_videos_channel_id_foreign');
        });
        Schema::dropIfExists('youtube_feeds_videos');
    }
}
