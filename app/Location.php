<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class Location extends Model
{

    /***********************************
     * ATTRIBUTES
     ***********************************/
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = array(
        'name',
        'floor',
        'description',
        'map_section_id',
    );

    /***********************************
     * RELATIONSHIPS
     ***********************************/
    public function mapSection()
    {
        return $this->belongsTo('App\MapSection');
    }

    public function quests()
    {
        return $this->hasMany('App\Quest');
    }

    /***********************************
     * SCOPES
     ***********************************/

    /***********************************
     * METHODS
     ***********************************/

    /***********************************
     * ACCESSORS
     ***********************************/

    public function getFloorNthAttribute()
    {
        $number = $this->floor;
        $ends = array('th','st','nd','rd','th','th','th','th','th','th');
        if (($number %100) >= 11 && ($number%100) <= 13)
            return $number. 'th';
        else
            return $number. $ends[$number % 10];

    }

    /***********************************
     * MUTATORS
     ***********************************/
}
