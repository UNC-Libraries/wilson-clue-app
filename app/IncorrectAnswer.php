<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class IncorrectAnswer extends Model
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
        'team_id',
        'question_id',
        'answer',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'judged' => 'boolean',
        ];
    }

    /***********************************
     * RELATIONSHIPS
     ***********************************/

    public function question()
    {
        return $this->belongsTo(\App\Question::class);
    }

    public function team()
    {
        return $this->belongsTo(\App\Team::class);
    }

    /***********************************
     * SCOPES
     ***********************************/

    public function scopeJudged($query)
    {
        return $query->where('judged', '=', 1);
    }

    public function scopeNotJudged($query)
    {
        return $query->where('judged', '=', 0);
    }

    public function scopeOfGame($query, $id)
    {
        return $query->whereHas('question', function ($query) use ($id) {
            $query->whereHas('quests', function ($query) use ($id) {
                $query->where('game_id', $id);
            });
        });
    }

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
