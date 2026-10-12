<?php

namespace App;

use App\Ldap\PlayerUser;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Log;
use LdapRecord\Laravel\Auth\AuthenticatesWithLdap;
use LdapRecord\Laravel\Auth\LdapAuthenticatable;
use LdapRecord\Models\ActiveDirectory\User;

class Player extends Authenticatable implements LdapAuthenticatable
{
    use AuthenticatesWithLdap, HasFactory;
    /***********************************
     * ATTRIBUTES
     ***********************************/

    /**
     * The additional attributes
     *
     * @var array
     */
    protected $appends = [
        'full_name',
        'class',
        'academic_group',
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

    /* Set default model values, if none is provided */
    protected $attributes = [
        'academic_group_code' => 'NONS',
        'class_code' => 'NONS',
    ];

    protected function casts(): array
    {
        return [
            'student' => 'boolean',
            'manual' => 'boolean',
        ];
    }

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
        'SILS' => 'School of Information and Library Science',
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

    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class);
    }

    /***********************************
     * SCOPES
     ***********************************/

    #[Scope]
    protected function ofGame($query, $gameId)
    {
        return $query->whereHas('teams', function ($scopeQuery) use ($gameId) {
            $scopeQuery->where('game_id', $gameId);
        });
    }

    #[Scope]
    protected function checkedIn($query)
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
    public function updateFromOnyen($onyen, $override_student = false): void
    {
        if ($this->validOnyen($onyen)) {
            $getPerson = PlayerUser::query()->where('uid', '=', $onyen)->get();
            $uncPerson = $getPerson->first();

            $this->onyen = $onyen;
            $this->firstName = $uncPerson->givenname[0];
            $this->lastName = $uncPerson->sn[0];
            $this->pid = $uncPerson->pid[0];
            $this->email = $uncPerson->mail[0];
            if (empty($uncPerson->uncstudentrecord[0])) {
                $this->academic_group_code = 'NONS';
                $this->class_code = 'NONS';
                $this->student = false;
            } else {
                $getStudentInfo = PlayerUser::query()->find($uncPerson->uncstudentrecord[0]);
                if (empty($getStudentInfo)) {
                    Log::warning('Unable to retrieve student info from ' . print_r($uncPerson, true));
                    $this->academic_group_code = 'NONS';
                    $this->class_code = 'NONS';
                } else {
                    $this->academic_group_code = $getStudentInfo->uncacademicgroupcode[0] || 'NONS';
                    $this->class_code = $getStudentInfo->unccareercode[0];
                }
                $this->student = true;
            }
        }

        if ($override_student) {
            $this->student = true;
        }
    }

    /**
     * Set player warnings
     *
     * @param Game|null $game
     * @return array
     */
    public function getWarnings(?Game $game = null): array
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
        // $this->teams->load();
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
    public function getFirstNameAttribute(?string $value): string
    {
        return $value ? ucfirst($value) : '';
    }

    /**
     * Get the player's last name.
     *
     * @param string $value
     * @return string
     */
    public function getLastNameAttribute(?string $value): string
    {
        return $value ? ucfirst($value) : '';
    }

    /**
     * Get the player's full name.
     *
     * @return string
     */
    public function getFullNameAttribute(): string
    {
        return $this->first_name.' '.$this->last_name;
    }

    /**
     * Get the player's class.
     *
     * @return string
     */
    public function getClassAttribute(): string
    {
        return self::CLASS_OPTIONS[$this->class_code ?? ''] ?? 'Not Found';
    }

    /**
     * Get the player's academic group.
     *
     * @return string
     */
    public function getAcademicGroupAttribute(): string
    {
        return self::ACADEMIC_GROUP_OPTIONS[$this->academic_group_code ?? ''] ?? 'Not Found';
    }

    /***********************************
     * MUTATORS
     ***********************************/

    /**
     * Set the player's first name.
     *
     * @param string $value
     * @return void
     */
    public function setFirstNameAttribute(string $value): void
    {
        $this->attributes['first_name'] = mb_convert_encoding(strtolower($value), 'UTF-8', 'ISO-8859-1');
    }

    /**
     * Set the player's last name.
     *
     * @param string $value
     * @return void
     */
    public function setLastNameAttribute(string $value): void
    {
        $this->attributes['last_name'] = mb_convert_encoding(strtolower($value), 'UTF-8', 'ISO-8859-1');
    }

    /**
     * Set the class code attribute
     *
     * @param  string  $value
     * @return void
     */
    public function setClassCodeAttribute(?string $value): void
    {
        $this->attributes['class_code'] = $value ? $value : '';
    }

    /**
     * Set the academic group code attribute
     *
     * @param  string  $value
     * @return void
     */
    public function setAcademicGroupCodeAttribute(?string $value): void
    {
        $this->attributes['academic_group_code'] = $value ? $value : '';
    }
}
