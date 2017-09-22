<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateIncorrectAnswersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('incorrect_answers', function(Blueprint $table){
            $table->increments('id');
            $table->integer('team_id');
            $table->integer('question_id');
            $table->string('answer');
            $table->integer('judged')->default(0);
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
        Schema::drop('incorrect_answers');
    }
}
