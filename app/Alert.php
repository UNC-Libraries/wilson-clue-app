<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

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
    protected $fillable = array(
        'message',
    );

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
