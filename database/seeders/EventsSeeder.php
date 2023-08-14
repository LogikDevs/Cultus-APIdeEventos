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
            'name' => "Lorem Ipsum Passage",
            'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
            'text' => 'Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis.',
            'start_date' => date('d-m-y H:i'),
            'end_date' => date('d-m-y H:i'),
            'privacity' => 'Public'
        ]);

        Events::factory(10)->create();
    }
}
