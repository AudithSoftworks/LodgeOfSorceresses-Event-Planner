<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTokenRelatedFieldsToUsersOauthTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('users_oauth', static function (Blueprint $table) {
            $table->string('token')->nullable()->after('verified');
            $table->timestamp('token_expires_at')->nullable()->after('token');
            $table->string('refresh_token')->nullable()->after('token_expires_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('users_oauth', static function (Blueprint $table) {
            $table->dropColumn(['token', 'token_expires_at', 'refresh_token']);
        });
    }
}
