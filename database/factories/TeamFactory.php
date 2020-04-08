<?php

use App\Models\Team;

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(Team::class, static function () {
    return [
        'name' => 'Team',
        'tier' => 4,
        'discord_role_id' => '123456789',
        'led_by' => 2,
        'created_by' => 2,
    ];
});
