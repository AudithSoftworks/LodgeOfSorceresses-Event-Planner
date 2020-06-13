<?php

use App\Models\Attendance;

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(Attendance::class, static function (\Faker\Generator $faker) {
    return [
        'text' => $faker->text,
        'text_for_forums' => $faker->text,
        'text_for_planner' => $faker->text,
        'discord_message_id' => $faker->numberBetween(),
        'gallery_image_ids' => null,
    ];
});
