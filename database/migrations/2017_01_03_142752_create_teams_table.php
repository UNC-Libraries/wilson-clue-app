<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTeamsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('teams', function(Blueprint $table){
            $table->increments('id');
            $table->integer('game_id');
            $table->string('name');
            $table->string('score')->default(0);
            $table->integer('suspect_id')->default(0);
            $table->integer('location_id')->default(0);
            $table->integer('evidence_id')->default(0);
            $table->timestamp('indictment_time');
            $table->boolean('waitlist')->default(1);
            $table->text('dietary')->nullable();
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
        Schema::drop('teams');
    }
}
