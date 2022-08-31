<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Alert extends Model
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
        'message',
    ];

    /***********************************
     * RELATIONSHIPS
     ***********************************/

    public function game()
    {
        return $this->belongsTo('App\Game');
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

    /***********************************
     * MUTATORS
     ***********************************/
}
