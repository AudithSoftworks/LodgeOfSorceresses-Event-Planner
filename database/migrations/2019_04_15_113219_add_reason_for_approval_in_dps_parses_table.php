<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddReasonForApprovalInDpsParsesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('dps_parses', static function (Blueprint $table) {
            $table->text('reason_for_disapproval')->after('approved_for_endgame_t2')->nullable();
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
            $table->dropColumn('reason_for_disapproval');
        });
    }
}
