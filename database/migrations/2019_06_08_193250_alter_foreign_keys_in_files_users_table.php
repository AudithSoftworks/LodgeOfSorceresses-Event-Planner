<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterForeignKeysInFilesUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('files_users', static function (Blueprint $table) {
            $table->dropForeign('files_users_file_hash_foreign');
            $table->dropForeign('files_users_user_id_foreign');

            $table->foreign('file_hash')->references('hash')->on('files')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('files_users', static function (Blueprint $table) {
            $table->dropForeign('files_users_file_hash_foreign');
            $table->dropForeign('files_users_user_id_foreign');

            $table->foreign('file_hash')->references('hash')->on('files')->onUpdate('cascade')->onDelete('restrict');
            $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('restrict');
        });
    }
}
