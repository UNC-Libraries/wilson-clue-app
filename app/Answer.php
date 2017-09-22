<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Answer extends Model
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
        'question_id',
        'text',
    );

    /***********************************
     * RELATIONSHIPS
     ***********************************/

    public function question()
    {
        return $this->belongsTo('App\Question');
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
