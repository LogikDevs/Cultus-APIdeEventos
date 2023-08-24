<?php

namespace Database\Factories;

use App\Models\Events;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
//use Illuminate\Support\Facades\Http;

class ParticipantsFactory extends Factory
{
    public function definition()
    {
        return [
            'fk_id_event' => Events::all()->random()->id,
            'fk_id_user' => User::all()->random()->id,
        ];
    }
}
