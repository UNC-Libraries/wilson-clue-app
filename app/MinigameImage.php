<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\File;

class MinigameImage extends Model
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
        'name',
        'year',
        'src',
    ];

    /***********************************
     * RELATIONSHIPS
     ***********************************/
    public function quests(): BelongsToMany
    {
        return $this->belongsToMany(\App\Quest::class);
    }

    /***********************************
     * SCOPES
     ***********************************/

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
    public function getSrcAttribute($value)
    {
        return env('PUBLIC_UPLOADS_PATH')."/$value";
    }

    /***********************************
     * MUTATORS
     ***********************************/
}
