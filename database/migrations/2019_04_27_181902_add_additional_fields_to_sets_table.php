<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAdditionalFieldsToSetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('sets', static function (Blueprint $table) {
            $table->string('type')->after('name');
            $table->string('location')->after('type');
            $table->text('bonus_item_1')->nullable()->after('location');
            $table->text('bonus_item_2')->nullable()->after('bonus_item_1');
            $table->text('bonus_item_3')->nullable()->after('bonus_item_2');
            $table->text('bonus_item_4')->nullable()->after('bonus_item_3');
            $table->text('bonus_item_5')->nullable()->after('bonus_item_4');
            $table->boolean('has_jewels')->default(false)->after('bonus_item_5');
            $table->boolean('has_weapons')->default(false)->after('has_jewels');
            $table->boolean('has_light_armor')->default(false)->after('has_weapons');
            $table->boolean('has_medium_armor')->default(false)->after('has_light_armor');
            $table->boolean('has_heavy_armor')->default(false)->after('has_medium_armor');
            $table->unsignedTinyInteger('traits_needed')->nullable()->after('has_heavy_armor');
            $table->boolean('pts')->default(false)->after('traits_needed');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('sets', static function (Blueprint $table) {
            $table->dropColumn([
                'location',
                'type',
                'bonus_item_1',
                'bonus_item_2',
                'bonus_item_3',
                'bonus_item_4',
                'bonus_item_5',
                'has_jewels',
                'has_weapons',
                'has_light_armor',
                'has_medium_armor',
                'has_heavy_armor',
                'traits_needed',
                'pts',
            ]);
        });
    }
}
