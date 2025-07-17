<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('incorrect_answers', function (Blueprint $table) {
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
     */
    public function down(): void
    {
        Schema::drop('incorrect_answers');
    }
};
