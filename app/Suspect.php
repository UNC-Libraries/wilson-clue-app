<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Suspect extends Model
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
        'machine',
        'profession',
        'bio',
        'quote',
    ];

    /**
     * The additional attributes
     *
     * @var array
     */
    protected $appends = [
        'bootstrap_color',
    ];

    /***********************************
     * RELATIONSHIPS
     ***********************************/
    public function quest()
    {
        return $this->hasMany('App\Quest');
    }

    /***********************************
     * SCOPES
     ***********************************/

    /***********************************
     * METHODS
     ***********************************/

    /***********************************
     * ACCESSORS
     ***********************************/

    /**
     * Get the bootstrap related color
     *
     * return @string
     */
    public function getBootstrapColorAttribute()
    {
        $bootstrapOptions = [
            'white' => 'default',
            'peacock' => 'primary',
            'green' => 'success',
            'mustard' => 'warning',
            'scarlet' => 'danger',
            'plum' => 'info',
        ];

        return $bootstrapOptions[$this->attributes['machine']];
    }

    public function getSideAttribute()
    {
        if (in_array($this->machine, ['plum', 'mustard', 'green'])) {
            return 'char-right-page';
        } else {
            return 'char-left-page';
        }
    }

    public function getImagePathAttribute()
    {
        return 'images/suspects/';
    }

    public function getFaceImageAttribute()
    {
        return $this->image_path.$this->machine.'_face.jpg';
    }

    public function getCardImageAttribute()
    {
        return $this->image_path.$this->machine.'_card.jpg';
    }

    public function getTinyImageAttribute()
    {
        return $this->image_path.$this->machine.'_tiny.jpg';
    }

    public function getLogoAttribute()
    {
        return $this->image_path.$this->machine.'_logo.jpg';
    }

    /***********************************
     * MUTATORS
     ***********************************/
}
