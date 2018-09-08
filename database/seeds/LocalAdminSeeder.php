<?php

use Illuminate\Database\Seeder;
use App\Agent;

class LocalAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Only run this if you're on a local environment
        if(env('APP_ENV') === 'local') {
            $agent = new Agent;
            $agent->onyen = 'admin';
            $agent->first_name = 'admin';
            $agent->last_name = 'admin';
            $agent->password = 'admin';
            $agent->admin = true;
            $agent->save();
        }
    }
}
