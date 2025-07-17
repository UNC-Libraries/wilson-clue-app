<?php

namespace App;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\File;

class Question extends Model
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
        'text',
        'type',
        'full_answer',
        'src',
        'location_id',
    ];

    protected $hidden = [
        'full_answer',
        'answers',
    ];

    protected function casts(): array
    {
        return [
            'type' => 'boolean',
        ];
    }

    /***********************************
     * RELATIONSHIPS
     ***********************************/

    public function answers(): HasMany
    {
        return $this->hasMany(\App\Answer::class);
    }

    public function quests(): BelongsToMany
    {
        return $this->belongsToMany(\App\Quest::class);
    }

    public function incorrectAnswers(): HasMany
    {
        return $this->hasMany(\App\IncorrectAnswer::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(\App\Location::class);
    }

    public function completedBy(): BelongsToMany
    {
        return $this->belongsToMany(\App\Team::class)->withTimestamps();
    }

    /***********************************
     * SCOPES
     ***********************************/
    public function scopeOfQuest($query, $questId)
    {
        return $query->whereHas('quests', function ($scopeQuery) use ($questId) {
            $scopeQuery->where('id', $questId);
        });
    }

    /***********************************
     * METHODS
     ***********************************/
    public function deleteImage()
    {
        $upload_path = config('filesystems.disks.public.root');
        $image_path = $this->attributes['src'];
        if (File::exists("$upload_path/$image_path")) {
            File::delete("$upload_path/$image_path");
        }
    }

    /***********************************
     * ACCESSORS
     ***********************************/

    /**
     * Get the list of incorrect, not judged answers for a team
     *
     * @return Collection
     */
    public function getNotJudgedAnswersAttribute()
    {
        return $this->incorrectAnswers->reject(function ($value, $key) {
            return $value->judged === true ||
                $this->completedBy->pluck('id')->contains($value->team_id) ||
                empty($value->team);
        });
    }

    /**
     * Check if the question needs judgement
     *
     * @return bool
     */
    public function getNeedsJudgementAttribute()
    {
        return ! $this->not_judged_answers->isEmpty();
    }

    public function getSrcAttribute($value)
    {
        return env('PUBLIC_UPLOADS_PATH')."/$value";
    }

    /***********************************
     * MUTATORS
     ***********************************/
}
