<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;
use App\Models\Events;

class EventsSeeder extends Seeder
{
    public function run()
    {
        DB::table('events')->insert([
            'name' => "EVENTO TESTING",
            'description' => 'Descripcion para el evento a testar',
            'text' => 'Texto para el evento a testar.',
            'start_date' => date('2025-12-01 00:00:00'),
            'end_date' => date('2025-12-31 00:00:00'),
            'private' => false
        ]);

        Events::factory(10)->create();
    }
}
