<?php

use Illuminate\Database\Seeder;
use App\Player;

class PlayerTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for ($x = 0; $x <= 1500; $x++) {
            $p = new Player;
            $p->email = str_random(12).'@gmail.com';
            $p->first_name = str_random(5);
            $p->last_name = str_random(8);
            $p->class_code = array_random(array_keys($p::CLASS_OPTIONS));
            $p->academic_group_code = array_random(array_keys($p::ACADEMIC_GROUP_OPTIONS));
            $p->save();
        }
    }
}
