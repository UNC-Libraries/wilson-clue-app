<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('games', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->boolean('active')->default(0);
            $table->boolean('students_only')->default(1);
            $table->integer('suspect_id')->default(0);
            $table->integer('location_id')->default(0);
            $table->integer('evidence_id')->default(0);
            $table->text('case_file_items')->nullable();
            $table->integer('evidence_location_id')->default(0);
            $table->integer('max_teams')->default(17);
            $table->integer('winning_team')->default(0);
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->boolean('registration')->default(0);
            $table->boolean('archive')->default(0);
            $table->string('flickr')->nullable();
            $table->string('flickr_start_img')->nullable();
            $table->text('special_thanks')->nullable();
            $table->text('team_accolades')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('games');
    }
};
