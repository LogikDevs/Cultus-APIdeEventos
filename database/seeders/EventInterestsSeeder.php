<?php

namespace Database\Seeders;

use App\Models\EventInterests;
use Illuminate\Database\Seeder;

class EventInterestsSeeder extends Seeder
{
    public function run()
    {
        EventInterests::factory(10)->create();
    }
}
