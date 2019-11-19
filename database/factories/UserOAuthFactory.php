<?php

use App\Models\User;
use App\Models\UserOAuth;
use App\Services\DiscordApi;
use Faker\Generator;

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(UserOAuth::class, static function (Generator $faker) {
    return [
        'user_id' => factory(User::class),
        'remote_provider' => 'discord',
        'remote_id' => $faker->unique()->numberBetween(2000000000),
    ];
});

$memberSecondaryGroups = [
    DiscordApi::ROLE_MEMBERS,
    DiscordApi::ROLE_INITIATE,
];

$factory->state(UserOAuth::class, 'member', [
    'remote_primary_group' => null,
    'remote_secondary_groups' => implode(',', $memberSecondaryGroups),
]);

$adminSecondaryGroups = [
    DiscordApi::ROLE_MEMBERS,
    DiscordApi::ROLE_INITIATE,
    DiscordApi::ROLE_MAGISTER_TEMPLI,
];

$factory->state(UserOAuth::class, 'admin-member', [
    'remote_primary_group' => null,
    'remote_secondary_groups' => implode(',', $adminSecondaryGroups),
]);

$soulshrivenSecondaryGroups = [
    DiscordApi::ROLE_SOULSHRIVEN,
    DiscordApi::ROLE_INITIATE_SHRIVEN,
];

$factory->state(UserOAuth::class, 'soulshriven', [
    'remote_primary_group' => null,
    'remote_secondary_groups' => implode(',', $soulshrivenSecondaryGroups),
]);
