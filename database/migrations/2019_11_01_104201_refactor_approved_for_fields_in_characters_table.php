<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RefactorApprovedForFieldsInCharactersTable extends Migration
{
    /**
     * @var \Illuminate\Database\Query\Builder
     */
    private $tableConnection;

    public function __construct()
    {
        $this->tableConnection = app('db.connection')->table('characters');
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('characters', static function (Blueprint $table) {
            $table->unsignedTinyInteger('approved_for_tier')->default(0)->after('skills');
        });

        $charactersWithSelectedTierApproval = $this->tableConnection->where('approved_for_t1', true)->get(['id'])->keyBy('id')->toArray();
        $this->tableConnection->whereIn('id', array_keys($charactersWithSelectedTierApproval))->update(['approved_for_tier' => 1]);

        $charactersWithSelectedTierApproval = $this->tableConnection->where('approved_for_t2', true)->get(['id'])->keyBy('id')->toArray();
        $this->tableConnection->whereIn('id', array_keys($charactersWithSelectedTierApproval))->update(['approved_for_tier' => 2]);

        $charactersWithSelectedTierApproval = $this->tableConnection->where('approved_for_t3', true)->get(['id'])->keyBy('id')->toArray();
        $this->tableConnection->whereIn('id', array_keys($charactersWithSelectedTierApproval))->update(['approved_for_tier' => 3]);

        $charactersWithSelectedTierApproval = $this->tableConnection->where('approved_for_t4', true)->get(['id'])->keyBy('id')->toArray();
        $this->tableConnection->whereIn('id', array_keys($charactersWithSelectedTierApproval))->update(['approved_for_tier' => 4]);

        Schema::table('characters', static function (Blueprint $table) {
            $table->dropColumn('approved_for_t1');
            $table->dropColumn('approved_for_t2');
            $table->dropColumn('approved_for_t3');
            $table->dropColumn('approved_for_t4');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('characters', static function (Blueprint $table) {
            $table->boolean('approved_for_t1')->after('skills')->default(false);
            $table->boolean('approved_for_t2')->after('approved_for_t1')->default(false);
            $table->boolean('approved_for_t3')->after('approved_for_t2')->default(false);
            $table->boolean('approved_for_t4')->after('approved_for_t3')->default(false);
        });

        $this->tableConnection->where('approved_for_tier', '>=', 1)->update(['approved_for_t1' => true]);
        $this->tableConnection->where('approved_for_tier', '>=', 2)->update(['approved_for_t2' => true]);
        $this->tableConnection->where('approved_for_tier', '>=', 3)->update(['approved_for_t3' => true]);
        $this->tableConnection->where('approved_for_tier', '>=', 4)->update(['approved_for_t4' => true]);

        Schema::table('characters', static function (Blueprint $table) {
            $table->dropColumn('approved_for_tier');
        });
    }
}
