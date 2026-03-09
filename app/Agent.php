<?php

namespace App;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\File;
use LdapRecord\Laravel\Auth\AuthenticatesWithLdap;
use LdapRecord\Laravel\Auth\LdapAuthenticatable;

class Agent extends Authenticatable implements LdapAuthenticatable
{
    use AuthenticatesWithLdap, HasFactory;
    /***********************************
     * ATTRIBUTES
     ***********************************/

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'onyen',
        'first_name',
        'last_name',
        'job_title',
        'title',
        'location',
        'retired',
        'bio',
        'web_display',
        'admin',
        'src',
    ];

    /**
     * The additional attributes
     *
     * @var array
     */
    protected $appends = [
        'full_name' => '',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'retired' => 'boolean',
            'web_display' => 'boolean',
            'admin' => 'boolean',
        ];
    }

    public function getLdapDomainColumn(): string
    {
        return 'onyen';
    }

    public function getLdapGuidColumn(): string
    {
        return 'objectguid';
    }

    /***********************************
     * RELATIONSHIPS
     ***********************************/

    /***********************************
     * SCOPES
     ***********************************/

    #[Scope]
    protected function active($query)
    {
        $query->where('retired', '=', 0)->where('web_display', '=', 1);
    }

    #[Scope]
    protected function retired($query)
    {
        $query->where('retired', '=', 1)->where('web_display', '=', 1);
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
     * Get the agents's first name.
     *
     * @param  string  $value
     * @return string
     */
    public function getFirstNameAttribute($value)
    {
        return ucfirst($value);
    }

    /**
     * Get the agents's last name.
     *
     * @param  string  $value
     * @return string
     */
    public function getLastNameAttribute($value)
    {
        return ucfirst($value);
    }

    /**
     * Get the agents's full name.
     *
     * @param  string  $value
     * @return string
     */
    public function getFullNameAttribute()
    {
        return $this->first_name.' '.$this->last_name;
    }

    public function getSrcAttribute($value)
    {
        return env('PUBLIC_UPLOADS_PATH')."/$value";
    }

    /***********************************
     * MUTATORS
     ***********************************/

    /**
     * Set the agents's first name.
     *
     * @param  string  $value
     * @return void
     */
    public function setFirstNameAttribute($value)
    {
        $this->attributes['first_name'] = utf8_encode(strtolower($value));
    }

    /**
     * Set the agents's last name.
     *
     * @param  string  $value
     * @return void
     */
    public function setLastNameAttribute($value)
    {
        $this->attributes['last_name'] = utf8_encode(strtolower($value));
    }
}
