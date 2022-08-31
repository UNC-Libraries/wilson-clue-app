<?php

namespace App;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
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

    protected $casts = [
        'type' => 'boolean',
    ];

    protected $hidden = [
        'full_answer',
        'answers',
    ];

    /***********************************
     * RELATIONSHIPS
     ***********************************/

    public function answers()
    {
        return $this->hasMany('App\Answer');
    }

    public function quests()
    {
        return $this->belongsToMany('App\Quest');
    }

    public function incorrectAnswers()
    {
        return $this->hasMany('App\IncorrectAnswer');
    }

    public function location()
    {
        return $this->belongsTo('App\Location');
    }

    public function completedBy()
    {
        return $this->belongsToMany('App\Team')->withTimestamps();
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
