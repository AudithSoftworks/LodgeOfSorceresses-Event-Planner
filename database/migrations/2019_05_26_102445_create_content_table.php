<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('content', static function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->string('name');
            $table->string('short_name')->nullable();
            $table->string('version')->nullable(); // '+1', '+2', '+3' or 'HM'
            $table->enum('type', ['midgame', 'endgame']);
            $table->enum('tier', [0, 1, 2])->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('content');
    }
}
