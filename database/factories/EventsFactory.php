<?php

namespace Database\Factories;

use App\Models\Events;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class EventsFactory extends Factory
{
    public function definition()
    {
        $description = $this->faker->paragraph();
        $description = Str::limit($description, 255);

        return [
            'name' => $this->faker->word(),
            'description' => $this->faker->sentence(),
            'text' => $description,
            'start_date' => $this->faker->dateTime(),
            'end_date' => $this->faker->dateTime(),
            'private' => $this->faker->boolean()
        ];
    }
}
