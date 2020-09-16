<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
            'remember_token' => Str::random(10),
        ];
    }

    public function soulshriven(): self
    {
        return $this->state([
            'name' => 'Soulshriven',
        ]);
    }

    public function member(): self
    {
        return $this->state([
            'name' => 'Member',
        ]);
    }

    public function admin(): self
    {
        return $this->state([
            'name' => 'Admin',
        ]);
    }
}
