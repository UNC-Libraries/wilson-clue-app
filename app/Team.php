<?php

namespace App;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Team extends Model
{
    const MINIMUM_PLAYERS = 4;

    use SoftDeletes;

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
        'dietary',
        'bonus_points',
    ];

    /**
     * The additional attributes
     *
     * @var array
     */
    protected $appends = [
        'indictment_made',
        'indictment_correct',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'indictment_time' => 'datetime',
            'evidence_selected_at' => 'datetime',
            'waitlist' => 'boolean',
            'score' => 'float',
        ];
    }

    /***********************************
     * ACCESSORS
     ***********************************/
    /**
     * Check if an indictment has been made.
     *
     * @return string
     */
    public function getIndictmentMadeAttribute()
    {
        return $this->indictment_time ? $this->indictment_time->year !== -1 : false;
    }

    /**
     * Check if an indictment is correct
     *
     * @return bool
     */
    public function getIndictmentCorrectAttribute()
    {
        if (empty($this->game->solution)) {
            return false;
        }

        return [
            'suspect' => $this->suspect_id,
            'location' => $this->location_id,
            'evidence' => $this->evidence_id,
        ] == $this->game->solution;
    }

    /**
     * Get the teams status
     *
     * @return array
     */
    public function getGameStatusAttribute()
    {
        $status = [];

        $completed = $this->completedQuests()->get();

        $notSet = $this->updated_at->addYears(1)->format('U');

        foreach ($this->game()->first()->quests()->with('suspect')->get() as $quest) {
            $status[] = [
                'name' => $quest->suspect->name,
                'color' => $completed->contains('id', $quest->id) ? $quest->suspect->machine : 'empty',
                'time' => $completed->contains('id', $quest->id) ? $completed->where('id', $quest->id)->first()->pivot->updated_at->format('U') : $notSet,
            ];
        }
        $status[] = [
            'name' => 'Indictment',
            'color' => $this->indictment_made ? 'indictment' : 'empty',
            'time' => $this->indictment_made ? $this->indictment_time->format('U') : $notSet,
        ];

        $status[] = [
            'name' => 'Evidence Room',
            'color' => empty($this->evidence_id) ? 'empty' : 'evidence',
            'time' => empty($this->evidence_selected_at) ? $notSet : $this->evidence_selected_at->format('U'),
        ];

        return collect($status);
    }

    /***********************************
     * MUTATORS
     ***********************************/

    /***********************************
     * RELATIONSHIPS
     ***********************************/

    public function game(): BelongsTo
    {
        return $this->belongsTo(\App\Game::class);
    }

    public function players(): BelongsToMany
    {
        return $this->belongsToMany(\App\Player::class);
    }

    public function checkedInPlayers(): BelongsToMany
    {
        return $this->belongsToMany(\App\Player::class)->checkedIn();
    }

    public function suspect(): BelongsTo
    {
        return $this->belongsTo(\App\Suspect::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(\App\Location::class);
    }

    public function evidence(): BelongsTo
    {
        return $this->belongsTo(\App\Evidence::class);
    }

    public function correctQuestions(): BelongsToMany
    {
        return $this->belongsToMany(\App\Question::class)->withTimestamps();
    }

    public function completedQuests(): BelongsToMany
    {
        return $this->belongsToMany(\App\Quest::class)->withTimestamps();
    }

    public function foundDna(): BelongsToMany
    {
        return $this->belongsToMany(\App\GhostDna::class)->withTimestamps();
    }

    public function incorrectAnswers(): HasMany
    {
        return $this->hasMany(\App\IncorrectAnswer::class);
    }

    /***********************************
     * SCOPES
     ***********************************/

    #[Scope]
    protected function registered($query)
    {
        return $query->where('waitlist', '=', 0);
    }

    #[Scope]
    protected function waitlist($query)
    {
        return $query->where('waitlist', '=', 1);
    }

    #[Scope]
    protected function active($query)
    {
        return $query->whereHas('game', function ($scopeQuery) {
            $scopeQuery->where('active', 1);
        });
    }

    /***********************************
     * METHODS
     ***********************************/

    public function checkPlayerWarnings()
    {
        foreach ($this->players as $player) {
            foreach ($player->warnings as $warning) {
                if (! $warning->isEmpty()) {
                    return true;
                }
            }
        }

        return false;
    }
}
