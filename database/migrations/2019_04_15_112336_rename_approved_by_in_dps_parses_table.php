<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameApprovedByInDpsParsesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('dps_parses', static function (Blueprint $table) {
            $table->dropForeign('dps_parses_approved_by_foreign');
            $table->renameColumn('approved_by', 'processed_by');
            $table->foreign('processed_by')->references('id')->on('users')->onUpdate('cascade')->onDelete('restrict');
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
            $table->dropForeign('dps_parses_processed_by_foreign');
            $table->renameColumn('processed_by', 'approved_by');
            $table->foreign('approved_by')->references('id')->on('users')->onUpdate('cascade')->onDelete('restrict');
        });
    }
}
