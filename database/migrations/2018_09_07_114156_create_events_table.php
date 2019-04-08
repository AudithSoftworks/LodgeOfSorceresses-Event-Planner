<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('events', static function (Blueprint $table) {
            $table->increments('id');
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->string('url')->nullable();
            $table->unsignedInteger('calendar_id')->nullable();
            $table->dateTimeTz('start_time')->nullable();
            $table->dateTimeTz('end_time')->nullable();
            $table->string('recurrence')->nullable();
            $table->boolean('rsvp')->default(false);
            $table->unsignedSmallInteger('rsvp_limit')->nullable();
            $table->boolean('locked')->default(false);
            $table->boolean('hidden')->default(false);
            $table->boolean('featured')->default(false);
            $table->timestamps();

            $table->foreign('calendar_id')->references('id')->on('calendars')->onUpdate('cascade')->onDelete('cascade');
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
            $table->dropForeign('events_calendar_id_foreign');
        });
        Schema::drop('events');
    }
}
