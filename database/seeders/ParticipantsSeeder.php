<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;
use App\Models\Participants;

class ParticipantsSeeder extends Seeder
{
    private function CreateUsers() {
        $faker = Faker::create();
        for ($i = 0; $i < 10; $i++) {
            DB::table('users')->insert([
                'name' => $faker->firstName,
                'surname' => $faker->lastName,
                'age' => $faker->numberBetween(0, 90),
                'email' => $faker->unique()->safeEmail,
                'password' => Hash::make('password')
            ]);
        }
    }

    public function run()
    {
        $this -> CreateUsers();
        Participants::factory(10)->create();
    }
}
