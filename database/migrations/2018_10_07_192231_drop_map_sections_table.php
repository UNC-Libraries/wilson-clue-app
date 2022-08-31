<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DropMapSectionsTable extends Migration
{
    const OLD_MAP_SECTIONS = [
        1 => 'first',
        2 => 'second',
        3 => 'ncc',
        4 => '2rr',
        5 => 'grand',
        6 => 'salt',
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Drop the map sections table
        Schema::dropIfExists('map_sections');

        // Create the map_section column to add values
        Schema::table('locations', function (Blueprint $table) {
            $table->string('map_section')->after('name');
        });

        // Insert the map section name where the id was in the locations table
        foreach (self::OLD_MAP_SECTIONS as $id => $name) {
            DB::table('locations')->where('map_section_id', $id)->update(['map_section' => $name]);
        }

        // Drop map section id column
        Schema::table('locations', function (Blueprint $table) {
            $table->dropColumn('map_section_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        // Create map section id column
        Schema::table('locations', function (Blueprint $table) {
            $table->integer('map_section_id')->after('name');
        });
        // Insert the old map section id
        foreach (self::OLD_MAP_SECTIONS as $id => $name) {
            DB::table('locations')->where('map_section', $name)->update(['map_section_id' => $id]);
        }
        // Drop the map section column
        Schema::table('locations', function (Blueprint $table) {
            $table->dropColumn('map_section');
        });
        // Create the map sections table
        Schema::create('map_sections', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('name');
        });
        foreach (config('map_sections') as $id => $section) {
            DB::table('map_sections')->insert(['id' => $id, 'name' => $section['svg_id']]);
        }
    }
}
