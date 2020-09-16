<?php

namespace Database\Factories;

use App\Models\File;
use Illuminate\Database\Eloquent\Factories\Factory;

class FileFactory extends Factory
{
    protected $model = File::class;

    public function definition(): array
    {
        return [
            'hash' => hash('md5', $this->faker->word),
            'disk' => 'local',
            'path' => '/some/irrelevant/path',
            'mime' => 'image/jpeg',
            'size' => 1024,
        ];
    }
}
