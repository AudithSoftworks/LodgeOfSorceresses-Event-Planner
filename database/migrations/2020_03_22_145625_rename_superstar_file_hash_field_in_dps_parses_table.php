<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameSuperstarFileHashFieldInDpsParsesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('dps_parses', static function (Blueprint $table) {
            $table->renameColumn('superstar_file_hash', 'info_file_hash');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('dps_parses', static function (Blueprint $table) {
            $table->renameColumn('info_file_hash', 'superstar_file_hash');
        });
    }
}
