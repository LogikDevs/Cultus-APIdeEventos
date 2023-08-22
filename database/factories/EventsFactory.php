<?php

namespace Database\Factories;

use App\Models\Events;
use Illuminate\Database\Eloquent\Factories\Factory;

class EventsFactory extends Factory
{
    public function definition()
    {
        return [
            'name' => $this->faker->word(),
            'description' => $this->faker->sentence(),
            'text' => $this->faker->paragraph(),
            'start_date' => $this->faker->dateTime(),
            'end_date' => $this->faker->dateTime(),
            'private' => $this->faker->boolean
        ];
    }
}
