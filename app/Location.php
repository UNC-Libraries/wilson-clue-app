<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

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
    protected $fillable = [
        'name',
        'floor',
        'description',
        'map_section',
    ];

    /***********************************
     * RELATIONSHIPS
     ***********************************/
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
        $ends = ['th', 'st', 'nd', 'rd', 'th', 'th', 'th', 'th', 'th', 'th'];
        if (($number % 100) >= 11 && ($number % 100) <= 13) {
            return $number.'th';
        } else {
            return $number.$ends[$number % 10];
        }
    }

    /***********************************
     * MUTATORS
     ***********************************/
}
