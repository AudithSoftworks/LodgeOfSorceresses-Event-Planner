<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSkillsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('skills', static function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->string('name');
            $table->string('slug');
            $table->unsignedSmallInteger('skill_line');
            $table->unsignedSmallInteger('parent')->nullable();
            $table->tinyInteger('type');
            $table->text('effect_1');
            $table->text('effect_2')->nullable();
            $table->string('cost')->nullable();
            $table->string('icon');
            $table->boolean('pts');
            $table->string('cast_time')->nullable();
            $table->string('target')->nullable();
            $table->string('range')->nullable();
            $table->unsignedTinyInteger('unlocks_at')->nullable();
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
        Schema::dropIfExists('skills');
    }
}
