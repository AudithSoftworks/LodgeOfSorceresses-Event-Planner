<?php

use App\Models\Character;
use App\Models\User;
use App\Singleton\ClassTypes;
use App\Singleton\RoleTypes;

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(Character::class, static function () {
    return [
        'user_id' => factory(User::class),
        'name' => 'A character name',
        'role' => RoleTypes::ROLE_TANK,
        'class' => ClassTypes::CLASS_DRAGONKNIGHT,
        'sets' => '1',
        'approved_for_tier' => 0,
    ];
});

$factory->state(Character::class, 'tank', [
    'role' => RoleTypes::ROLE_TANK,
    'class' => ClassTypes::CLASS_DRAGONKNIGHT,
]);

$factory->state(Character::class, 'healer', [
    'role' => RoleTypes::ROLE_HEALER,
    'class' => ClassTypes::CLASS_TEMPLAR,
]);

$factory->state(Character::class, 'magdd', [
    'role' => RoleTypes::ROLE_MAGICKA_DD,
    'class' => ClassTypes::CLASS_SORCERER,
]);

$factory->state(Character::class, 'stamdd', [
    'role' => RoleTypes::ROLE_STAMINA_DD,
    'class' => ClassTypes::CLASS_NECROMANCER,
]);

$factory->state(Character::class, 'tier-1', [
    'approved_for_tier' => 1,
]);

$factory->state(Character::class, 'tier-2', [
    'approved_for_tier' => 2,
]);

$factory->state(Character::class, 'tier-3', [
    'approved_for_tier' => 3,
]);

$factory->state(Character::class, 'tier-4', [
    'approved_for_tier' => 4,
]);
