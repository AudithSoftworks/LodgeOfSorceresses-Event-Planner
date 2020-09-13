<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\UserOAuth;
use App\Services\DiscordApi;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserOAuthFactory extends Factory
{
    protected $model = UserOAuth::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory()->make(),
            'remote_provider' => 'discord',
            'remote_id' => $this->faker->unique()->numberBetween(2000000000),
        ];
    }

    public function soulshriven(): self
    {
        return $this->state([
            'remote_primary_group' => null,
            'remote_secondary_groups' => implode(',', [
                DiscordApi::ROLE_SOULSHRIVEN,
                DiscordApi::ROLE_INITIATE_SHRIVEN,
            ]),
        ]);
    }

    public function member(): self
    {
        return $this->state([
            'remote_primary_group' => null,
            'remote_secondary_groups' => implode(',', [
                DiscordApi::ROLE_MEMBERS,
                DiscordApi::ROLE_INITIATE,
            ]),
        ]);
    }

    public function admin(): self
    {
        return $this->state([
            'remote_primary_group' => null,
            'remote_secondary_groups' => implode(',', [
                DiscordApi::ROLE_MEMBERS,
                DiscordApi::ROLE_INITIATE,
                DiscordApi::ROLE_MAGISTER_TEMPLI,
            ]),
        ]);
    }
}
