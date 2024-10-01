<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;
use LdapRecord\Laravel\Auth\AuthenticatesWithLdap;
use LdapRecord\Laravel\Auth\LdapAuthenticatable;
use LdapRecord\Models\ActiveDirectory\User;

class Player extends  Authenticatable implements LdapAuthenticatable
{
    use AuthenticatesWithLdap;
    /***********************************
     * ATTRIBUTES
     ***********************************/

    /**
     * The additional attributes
     *
     * @var array
     */
    protected $appends = [
        'full_name' => '',
        'class' => '',
        'academic_group' => '',
    ];

    protected $fillable = [
        'first_name',
        'last_name',
        'onyen',
        'pid',
        'email',
        'academic_group_code',
        'class_code',
        'password',
        'manual',
        'student',
        'checked_in',
    ];

    protected $casts = [
        'student' => 'boolean',
        'manual' => 'boolean',
    ];

    const ACADEMIC_GROUP_OPTIONS = [
        'LAW' => 'School of Law',
        'GRAD' => 'Graduate School',
        'SPH' => 'School of Public Health',
        'SSW' => 'School of Social Work',
        'SOP' => 'School of Pharmacy',
        'SOM' => 'School of Medicine',
        'CAS' => 'College of Arts and Sciences',
        'SOE' => 'School of Education',
        'KFBS' => 'Kenan-Flagler Business School',
        'SON' => 'School of Nursing',
        'SILS' => 'School of Informaition and Library Science',
        'SOJ' => 'School of Journalism',
        'SOG' => 'School of Government',
        'SOD' => 'School of Dentistry',
        'OUR' => 'Office of the University Registrar',
        'NONS' => 'Non Student',
        '' => 'Not Found',
    ];

    const CLASS_OPTIONS = [
        'UGRD' => 'Undergraduate',
        'GRAD' => 'Graduate',
        'MED' => 'Medical',
        'DENT' => 'Dental',
        'LAW' => 'Law',
        'PHCY' => 'Pharmacy',
        'NONS' => 'Non Student',
        '' => 'Not Found',
    ];

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

    public function teams()
    {
        return $this->belongsToMany(Team::class);
    }

    /***********************************
     * SCOPES
     ***********************************/

    public function scopeOfGame($query, $gameId)
    {
        return $query->whereHas('teams', function ($scopeQuery) use ($gameId) {
            $scopeQuery->where('game_id', $gameId);
        });
    }

    public function scopeCheckedIn($query)
    {
        return $query->where('checked_in', true);
    }

    /***********************************
     * METHODS
     ***********************************/

    /**
     * Check for valid onyen
     *
     * @param onyen string
     * @return bool
     */
    public function validOnyen($onyen)
    {
        $search = User::where('uid', '=', $onyen)->get();

        return ! $search->isEmpty();
    }

    /**
     * Update a player from an onyen
     *
     * @param onyen string
     */
    public function updateFromOnyen($onyen, $override_student = false)
    {
        if ($this->validOnyen($onyen)) {
           // $getPerson = Adldap::getProvider('people')->search()->where('uid', '=', $onyen)->get();
            $getPerson = User::where('uid', '=', $onyen)->get();
            $uncPerson = $getPerson->first();
            json_encode($uncPerson, JSON_PRETTY_PRINT); exit;
            $this->onyen = $onyen;
            $this->firstName = $uncPerson->givenname[0];
            $this->lastName = $uncPerson->sn[0];
            $this->pid = $uncPerson->pid[0];
            $this->email = $uncPerson->mail[0];
        }

        if (empty($uncPerson->uncstudentrecord[0])) {
            $this->academic_group_code = 'NONS';
            $this->class_code = 'NONS';
            $this->student = false;
        } else {
            $getStudentInfo = User::find($uncPerson->uncstudentrecord[0]);
            $this->academic_group_code = $getStudentInfo->uncacademicgroupcode[0];
            $this->class_code = $getStudentInfo->unccareercode[0];
            $this->student = true;
        }

        if ($override_student) {
            $this->student = true;
        }
    }

    /**
     * Set player warnings
     *
     * @return array
     */
    public function getWarnings(Game $game = null)
    {
        $warnings = [];

        if (! $this->validOnyen($this->onyen)) {
            $warnings[] = 'enlist.add_player.onyen_not_found';
        }

        // Check that player hasn't been checked in
        if ($this->checked_in) {
            $warnings[] = 'enlist.add_player.previous';
        }

        // Checks that player isn't already registered for this game
        //$this->teams->load();
        if ($this->teams) {
            if ($this->teams->pluck('game.id')->contains($game->id)) {
                $warnings[] = 'enlist.add_player.current';
            }
        }

        // Check that player is a student (for student only games)
        if ($game) {
            if ($game->students_only && ! $this->student) {
                $warnings[] = 'enlist.add_player.not_student';
            }
        }

        return $warnings;
    }

    /***********************************
     * ACCESSORS
     ***********************************/

    /**
     * Get the player's first name.
     *
     * @param  string  $value
     * @return string
     */
    public function getFirstNameAttribute($value)
    {
        return ucfirst($value);
    }

    /**
     * Get the player's last name.
     *
     * @param  string  $value
     * @return string
     */
    public function getLastNameAttribute($value)
    {
        return ucfirst($value);
    }

    /**
     * Get the player's full name.
     *
     * @param  string  $value
     * @return string
     */
    public function getFullNameAttribute()
    {
        return $this->first_name.' '.$this->last_name;
    }

    /**
     * Get the player's class.
     *
     * @param  string  $value
     * @return string
     */
    public function getClassAttribute($all = false)
    {
        return self::CLASS_OPTIONS[$this->class_code];
    }

    /**
     * Get the player's academic group.
     *
     * @param  string  $value
     * @return string
     */
    public function getAcademicGroupAttribute()
    {
        return self::ACADEMIC_GROUP_OPTIONS[$this->academic_group_code];
    }

    /***********************************
     * MUTATORS
     ***********************************/

    /**
     * Set the player's first name.
     *
     * @param  string  $value
     * @return void
     */
    public function setFirstNameAttribute($value)
    {
        $this->attributes['first_name'] = utf8_encode(strtolower($value));
    }

    /**
     * Set the player's last name.
     *
     * @param  string  $value
     * @return void
     */
    public function setLastNameAttribute($value)
    {
        $this->attributes['last_name'] = utf8_encode(strtolower($value));
    }

    /**
     * Set the class code attribute
     *
     * @param  string  $value
     * @return void
     */
    public function setClassCodeAttribute($value)
    {
        $this->attributes['class_code'] = $value ? $value : '';
    }

    /**
     * Set the academic group code attribute
     *
     * @param  string  $value
     * @return void
     */
    public function setAcademicGroupCodeAttribute($value)
    {
        $this->attributes['academic_group_code'] = $value ? $value : '';
    }
}
