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
        Schema::create('agents', function (Blueprint $table) {
            $table->increments('id');
            $table->string('onyen')->nullable();
            $table->integer('pid')->nullable();
            $table->string('email')->nullable();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('password')->nullable();
            $table->string('remember_token')->nullable();
            $table->string('title')->nullable();
            $table->string('job_title')->nullable();
            $table->string('location')->nullable();
            $table->string('src')->nullable();
            $table->boolean('retired')->default(0);
            $table->boolean('web_display')->default(0);
            $table->boolean('admin')->default(0);
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
        Schema::drop('agents');
    }
};
