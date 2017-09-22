<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;

class Evidence extends Model
{
    /***********************************
     * ATTRIBUTES
     ***********************************/

    /**
     * The additional attributes
     *
     * @var array
     */

    protected $fillable = array(
        'title',
        'src'
    );


    /***********************************
     * RELATIONSHIPS
     ***********************************/
    public function games()
    {
        return $this->belongsToMany('App\Game');
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
