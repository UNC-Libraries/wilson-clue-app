<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\File;

class Evidence extends Model
{
    use HasFactory;

    /***********************************
     * ATTRIBUTES
     ***********************************/

    /**
     * The additional attributes
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'src',
    ];

    /***********************************
     * RELATIONSHIPS
     ***********************************/
    public function games(): BelongsToMany
    {
        return $this->belongsToMany(Game::class);
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
