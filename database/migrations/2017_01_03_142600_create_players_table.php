<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePlayersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('players', function(Blueprint $table){
            $table->increments('id');
            $table->string('onyen')->nullable();
            $table->integer('pid')->nullable();
            $table->string('email');
            $table->string('password')->nullable();
            $table->string('remember_token')->nullable();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('class_code');
            $table->string('academic_group_code');
            $table->boolean('checked_in')->default(0);
            $table->boolean('student')->default(0);
            $table->boolean('manual')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('players');
    }
}
