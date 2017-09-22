<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;
use Carbon\Carbon;
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
    protected $fillable = array(
        'name',
        'year',
        'src',
    );

    /***********************************
     * RELATIONSHIPS
     ***********************************/
    public function quests()
    {
        return $this->belongsToMany('App\Quest');
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
        if(File::exists("$upload_path/$image_path")){
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
