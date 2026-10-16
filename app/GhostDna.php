<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class GhostDna extends Model
{
    use HasFactory;

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

    public function teams(): BelongsToMany
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
