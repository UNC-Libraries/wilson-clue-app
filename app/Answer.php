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
    protected $fillable = [
        'question_id',
        'text',
    ];

    /***********************************
     * RELATIONSHIPS
     ***********************************/

    public function question()
    {
        return $this->belongsTo(\App\Question::class);
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
