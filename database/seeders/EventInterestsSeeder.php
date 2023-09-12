<?php

namespace Database\Seeders;

use App\Models\EventInterests;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EventInterestsSeeder extends Seeder
{
    private function CreateInterests() {
        DB::table('interest_label')->insert([
            ['interest' => 'arte'],
            ['interest' => 'gastronomia'],
            ['interest' => 'deportes'],
            ['interest' => 'historia'],
            ['interest' => 'musica'],
            ['interest' => 'jardineria'],
            ['interest' => 'tecnologia'],
            ['interest' => 'astronomia'],
            ['interest' => 'lectura'],
            ['interest' => 'cine'],
        ]);
    }

    public function run()
    {
        $this -> CreateInterests();
        EventInterests::factory(10)->create();
    }
}
