<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Quest extends Model
{
    use SoftDeletes;

    /***********************************
     * ATTRIBUTES
     ***********************************/

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'type',
        'location_id',
        'suspect_id',

    ];

    protected $appends = [
        'types',
    ];

    /***********************************
     * RELATIONSHIPS
     ***********************************/
    public function location()
    {
        return $this->belongsTo('App\Location');
    }

    public function suspect()
    {
        return $this->belongsTo('App\Suspect');
    }

    public function questions()
    {
        return $this->belongsToMany('App\Question')->withPivot('order')->orderBy('order');
    }

    public function minigameImages()
    {
        return $this->belongsToMany('App\MinigameImage');
    }

    public function completedBy()
    {
        return $this->belongsToMany('App\Team')->registered()->withTimestamps();
    }

    public function game()
    {
        return $this->belongsTo('App\Game');
    }

    /***********************************
     * SCOPES
     ***********************************/
    public function scopeMinigameType($query)
    {
        return $query->where('type', '=', 'minigame');
    }

    public function scopeQuestionType($query)
    {
        return $query->where('type', '=', 'question');
    }

    /***********************************
     * METHODS
     ***********************************/

    /***********************************
     * ACCESSORS
     ***********************************/

    public function getTypesAttribute()
    {
        return $this->attributes['types'] = [
            'question' => 'Question',
            'minigame' => 'First Floor Minigame',
        ];
    }

    public function getTeamCompletedAttribute($teamId)
    {
        return in_array($teamId, $this->completedBy()->pluck('id')->all());
    }

    public function getNeedsJudgementAttribute()
    {
        return ! $this->questions->where('needs_judgement', true)->isEmpty();
    }

    /***********************************
     * MUTATORS
     ***********************************/
}
