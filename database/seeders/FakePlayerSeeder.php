<?php

namespace Database\Seeders;

use App\Player;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class FakePlayerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Only run this if you're on a local environment
        if (env('APP_ENV') === 'local') {
            for ($x = 0; $x <= 10; $x++) {
                $p = new Player;
                $p->email = str_random(12).'@fake.fake';
                $p->onyen = 'player' . $x;
                $p->password = Hash::make('player');
                $p->first_name = str_random(5);
                $p->last_name = str_random(8);
                $p->class_code = array_random(array_keys($p::CLASS_OPTIONS));
                $p->academic_group_code = array_random(array_keys($p::ACADEMIC_GROUP_OPTIONS));
                $p->save();
            }
        }
    }
}
