<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSuspectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('suspects', function(Blueprint $table){
            $table->increments('id');
            $table->string('name');
            $table->string('machine');
            $table->string('profession');
            $table->text('bio');
            $table->string('quote');
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
        Schema::drop('suspects');
    }
}
