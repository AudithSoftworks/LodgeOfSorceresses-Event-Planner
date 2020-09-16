<?php

namespace Database\Factories;

use App\Models\Character;
use App\Models\User;
use App\Singleton\ClassTypes;
use App\Singleton\RoleTypes;
use Illuminate\Database\Eloquent\Factories\Factory;

class CharacterFactory extends Factory
{
    protected $model = Character::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory()->make(),
            'name' => 'A character name',
            'role' => RoleTypes::ROLE_TANK,
            'class' => ClassTypes::CLASS_DRAGONKNIGHT,
            'sets' => '1',
            'approved_for_tier' => 0,
        ];
    }

    public function role(int $role, int $class): self
    {
        return $this->state([
            'role' => $role,
            'class' => $class,
        ]);
    }

    public function tank(): self
    {
        return $this->state([
            'role' => RoleTypes::ROLE_TANK,
            'class' => ClassTypes::CLASS_DRAGONKNIGHT,
        ]);
    }

    public function healer(): self
    {
        return $this->state([
            'role' => RoleTypes::ROLE_HEALER,
            'class' => ClassTypes::CLASS_TEMPLAR,
        ]);
    }

    public function magdd(): self
    {
        return $this->state([
            'role' => RoleTypes::ROLE_MAGICKA_DD,
            'class' => ClassTypes::CLASS_SORCERER,
        ]);
    }

    public function stamdd(): self
    {
        return $this->state([
            'role' => RoleTypes::ROLE_STAMINA_DD,
            'class' => ClassTypes::CLASS_NECROMANCER,
        ]);
    }

    public function tier(int $tier): self
    {
        return $this->state([
            'approved_for_tier' => $tier,
        ]);
    }
}
