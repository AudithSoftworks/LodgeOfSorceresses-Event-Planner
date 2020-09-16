<?php

namespace Database\Factories;

use App\Models\Attendance;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceFactory extends Factory
{
    protected $model = Attendance::class;

    public function definition(): array
    {
        return [
            'text' => $this->faker->text,
            'text_for_forums' => $this->faker->text,
            'text_for_planner' => $this->faker->text,
            'discord_message_id' => $this->faker->numberBetween(),
            'gallery_image_ids' => null,
        ];
    }
}
