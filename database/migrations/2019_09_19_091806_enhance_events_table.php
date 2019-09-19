<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class EnhanceEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('events', static function (Blueprint $table) {
            $table->unsignedSmallInteger('content_id')->after('id');
            $table->tinyInteger('content_tier_adjustment')->default(0)->after('recurrence');
            $table->boolean('auto_checkin_options')->default(0)->after('rsvp_limit');
            $table->unsignedSmallInteger('mandated_team_id')->nullable();

            $table->foreign('content_id')->references('id')->on('content')->onUpdate('cascade')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('events', static function (Blueprint $table) {
            $table->dropForeign('events_content_id_foreign');

            $table->dropColumn('content_id');
            $table->dropColumn('content_tier_adjustment');
            $table->dropColumn('auto_checkin_options');
            $table->dropColumn('mandated_team_id');
        });
    }
}
