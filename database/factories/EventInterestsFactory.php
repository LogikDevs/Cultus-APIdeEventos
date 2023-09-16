<?php

namespace Database\Factories;

use App\Models\Events;
use App\Models\EventInterests;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Http;

class EventInterestsFactory extends Factory
{
    public function definition()
    {
        return [
            'fk_id_event' => Events::all()->random()->id,
            'fk_id_label' => random_int(1, 10),
        ];
    }
}
