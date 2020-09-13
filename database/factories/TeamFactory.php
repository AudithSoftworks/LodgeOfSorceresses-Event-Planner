<?php

namespace Database\Factories;

use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

class TeamFactory extends Factory
{
    protected $model = Team::class;

    public function definition(): array
    {
        return [
            'name' => 'Team',
            'tier' => 4,
            'discord_role_id' => '123456789',
            'led_by' => 2,
            'created_by' => 2,
        ];
    }
}
