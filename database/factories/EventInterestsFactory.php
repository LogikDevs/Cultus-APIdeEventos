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
            $response = Http::get('http://localhost:8000/api/v1/interest');
            if ($response->successful()) {
                $interests = $response->json();
                $interest = collect($interests)->random();
                return [
                    'fk_id_label' => $interest['id_label'],
                    'fk_id_event' => Events::all()->random()->id
                ];
            } else {
                return [];
            }
        return [
            'fk_id_event' => Events::all()->random()->id,
            'fk_id_label' => interest::all()->random()->id_label
        ];
    }
}
