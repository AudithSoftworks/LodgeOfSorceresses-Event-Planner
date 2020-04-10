<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendancesUsersTable extends Migration
{
    public function up(): void
    {
        Schema::create('attendances_users', static function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('attendance_id');
            $table->unsignedInteger('user_id');

            $table->foreign('attendance_id')->references('id')->on('attendances')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('attendances_users', static function (Blueprint $table) {
            $table->dropForeign('attendances_users_attendance_id_foreign');
            $table->dropForeign('attendances_users_user_id_foreign');
        });
        Schema::drop('attendances_users');
    }
}
