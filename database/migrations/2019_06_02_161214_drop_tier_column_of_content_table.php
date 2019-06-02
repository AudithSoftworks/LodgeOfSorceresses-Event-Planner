<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropTierColumnOfContentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('content', static function (Blueprint $table) {
            $table->dropColumn('tier');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('content', static function (Blueprint $table) {
            $table->enum('tier', [0, 1, 2])->nullable();
        });
    }
}
