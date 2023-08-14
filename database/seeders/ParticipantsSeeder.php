<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Participants;

class ParticipantsSeeder extends Seeder
{
    public function run()
    {
        Participants::factory(10)->create();
    }
}
