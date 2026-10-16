<?php

namespace App;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IncorrectAnswer extends Model
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

    public function question(): BelongsTo
    {
        return $this->belongsTo(\App\Question::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(\App\Team::class);
    }

    /***********************************
     * SCOPES
     ***********************************/

    #[Scope]
    protected function judged($query)
    {
        return $query->where('judged', '=', 1);
    }

    #[Scope]
    protected function notJudged($query)
    {
        return $query->where('judged', '=', 0);
    }

    #[Scope]
    protected function ofGame($query, $id)
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
