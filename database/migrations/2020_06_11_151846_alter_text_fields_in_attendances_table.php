<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTextFieldsInAttendancesTable extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', static function (Blueprint $table) {
            $table->text('text_for_forums')->after('text');
            $table->text('text_for_planner')->after('text_for_forums');
        });
    }

    public function down(): void
    {
        Schema::table('attendances', static function (Blueprint $table) {
            $table->dropColumn('text_for_forums');
            $table->dropColumn('text_for_planner');
        });
    }
}
