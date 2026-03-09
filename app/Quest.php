<?php

namespace App;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Quest extends Model
{
    use HasFactory, SoftDeletes;

    /***********************************
     * ATTRIBUTES
     ***********************************/

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
    public function location(): BelongsTo
    {
        return $this->belongsTo(\App\Location::class);
    }

    public function suspect(): BelongsTo
    {
        return $this->belongsTo(\App\Suspect::class);
    }

    public function questions(): BelongsToMany
    {
        return $this->belongsToMany(\App\Question::class)->withPivot('order')->orderBy('order');
    }

    public function minigameImages(): BelongsToMany
    {
        return $this->belongsToMany(\App\MinigameImage::class);
    }

    public function completedBy(): BelongsToMany
    {
        return $this->belongsToMany(\App\Team::class)->registered()->withTimestamps();
    }

    public function game(): BelongsTo
    {
        return $this->belongsTo(\App\Game::class);
    }

    /***********************************
     * SCOPES
     ***********************************/
    #[Scope]
    protected function minigameType($query)
    {
        return $query->where('type', '=', 'minigame');
    }

    #[Scope]
    protected function questionType($query)
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
