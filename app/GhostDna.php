<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GhostDna extends Model
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
        'sequence',
        'pair',
    ];

    /***********************************
     * RELATIONSHIPS
     ***********************************/

    public function teams()
    {
        return $this->belongsToMany(\App\Team::class)->withTimestamps();
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
    public function getFoundStatsAttribute()
    {
        $teams = $this->teams;
        $teams->load('game');

        return $teams->groupBy('game.name');
    }

    /***********************************
     * MUTATORS
     ***********************************/
}
