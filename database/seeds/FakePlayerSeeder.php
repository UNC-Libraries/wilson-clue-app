<?php

use App\Player;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FakePlayerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Only run this if you're on a local environment
        if (env('APP_ENV') === 'local') {
            $max_player_id = $users = DB::table('player_team')
                ->select(DB::raw('max(player_id) as max_player_id'))->first()->max_player_id;
            for ($x = 0; $x <= $max_player_id; $x++) {
                $p = new Player;
                $p->email = str_random(12).'@fake.fake';
                $p->first_name = str_random(5);
                $p->last_name = str_random(8);
                $p->class_code = array_random(array_keys($p::CLASS_OPTIONS));
                $p->academic_group_code = array_random(array_keys($p::ACADEMIC_GROUP_OPTIONS));
                $p->save();
            }
        }
    }
}
