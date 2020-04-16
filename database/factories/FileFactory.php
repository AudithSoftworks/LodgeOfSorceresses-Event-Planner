<?php

use App\Models\File;

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(File::class, static function (Faker\Generator $faker) {
    return [
        'hash' => hash('md5', $faker->word),
        'disk' => 'local',
        'path' => '/some/irrelevant/path',
        'mime' => 'image/jpeg',
        'size' => 1024,
    ];
});
