<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('agents', function (Blueprint $table) {
            $table->string('objectguid')->unique()->nullable()->after('id');
        });

        Schema::table('players', function (Blueprint $table) {
            $table->string('objectguid')->unique()->nullable()->after('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('agents', function (Blueprint $table) {
            $table->dropColumn('objectguid');
        });

        Schema::table('players', function (Blueprint $table) {
            $table->dropColumn('objectguid');
        });
    }
};
