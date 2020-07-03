<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddThumbnailFieldToYoutubeFeedsChannelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('youtube_feeds_channels', static function (Blueprint $table) {
            $table->string('thumbnail')->after('url');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('youtube_feeds_channels', static function (Blueprint $table) {
            $table->dropColumn('thumbnail');
        });
    }
}
