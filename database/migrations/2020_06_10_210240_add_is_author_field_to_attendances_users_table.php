<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsAuthorFieldToAttendancesUsersTable extends Migration
{
    public function up(): void
    {
        Schema::table('attendances_users', static function (Blueprint $table) {
            $table->boolean('is_author')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('attendances_users', static function (Blueprint $table) {
            $table->dropColumn('is_author');
        });
    }
}
