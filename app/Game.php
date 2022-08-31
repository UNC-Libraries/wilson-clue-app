<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Game extends Model
{
    use SoftDeletes;

    /***********************************
     * ATTRIBUTES
     ***********************************/

    /**
     * The additional attributes
     *
     * @var array
     */
    protected $appends = [
        'spots_left' => 0,
        'inProgress' => 0,
        'solution' => [],
    ];

    /**
     * The fillable attributes
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'suspect_id',
        'location_id',
        'evidence_id',
        'max_teams',
        'winning_team',
        'start_time',
        'end_time',
        'registration',
        'flickr',
        'flickr_start_img',
        'special_thanks',
        'team_accolades',
        'archive',
        'case_file_items',
        'created_at',
        'updated_at',
        'evidence_location_id',
        'active',
        'students_only',
    ];

    /**
     * Casts
     *
     * @var array
     */
    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'active' => 'boolean',
        'students_only' => 'boolean',    ];

    /***********************************
     * RELATIONSHIPS
     ***********************************/
    public function teams()
    {
        return $this->hasMany(\App\Team::class, 'game_id', 'id');
    }

    public function registeredTeams()
    {
        return $this->hasMany(\App\Team::class, 'game_id', 'id')->registered();
    }

    public function waitlistTeams()
    {
        return $this->hasMany(\App\Team::class, 'game_id', 'id')->waitlist();
    }

    public function winningTeam()
    {
        return $this->hasOne(\App\Team::class, 'id', 'winning_team');
    }

    public function evidenceLocation()
    {
        return $this->belongsTo(\App\Location::class, 'evidence_location_id');
    }

    public function geographicInvestigationLocation()
    {
        return $this->belongsTo(\App\Location::class, 'geographic_investigation_location_id');
    }

    public function solutionSuspect()
    {
        return $this->hasOne(\App\Suspect::class, 'id', 'suspect_id');
    }

    public function solutionLocation()
    {
        return $this->hasOne(\App\Location::class, 'id', 'location_id');
    }

    public function solutionEvidence()
    {
        return $this->hasOne(\App\Evidence::class, 'id', 'evidence_id');
    }

    public function quests()
    {
        return $this->hasMany(\App\Quest::class);
    }

    public function evidence()
    {
        return $this->belongsToMany(\App\Evidence::class);
    }

    public function alerts()
    {
        return $this->hasMany(\App\Alert::class);
    }

    /***********************************
     * SCOPES
     ***********************************/
    public function scopeActive($query)
    {
        return $query->where('active', 1);
    }

    public function scopeInProgress($query)
    {
        return $query->where('start_time', '<', date('Y-m-d H:i:s'))->where('end_time', '>', date('Y-m-d H:i:s'))->orderBy('start_time', 'desc');
    }

    public function scopeArchived($query)
    {
        return $query->where('archive', 1)->orderBy('start_time', 'desc');
    }

    /***********************************
     * METHODS
     ***********************************/

    /***********************************
     * ACCESSORS
     ***********************************/
    /**
     * Set the spots left in registration
     *
     * @return string
     */
    public function getSpotsLeftAttribute()
    {
        return $this->attributes['max_teams'] - $this->teams()->where('waitlist', '=', 0)->get()->count();
    }

    /**
     * Set and active (in progress) game
     *
     * @return string
     */
    public function getInProgressAttribute()
    {
        return time() > $this->start_time->getTimestamp() && time() < $this->end_time->getTimestamp() ? true : false;
    }

    public function getCaseFileItemsAttribute($value)
    {
        return json_decode($value);
    }

    public function getStatusTextAttribute()
    {
        if ($this->inProgress) {
            return 'In Progress';
        } elseif ($this->active) {
            return 'Current (active)';
        } elseif ($this->archive) {
            return 'Archived';
        } else {
            return 'Dormant';
        }
    }

    /**
     * Set the solution
     *
     * @return array
     */
    public function getSolutionAttribute()
    {
        return ['suspect' => $this->suspect_id, 'location' => $this->location_id, 'evidence' => $this->evidence_id];
    }

    /***********************************
     * MUTATORS
     ***********************************/
    public function setCaseFileItemsAttribute($value)
    {
        $this->attributes['case_file_items'] = json_encode($value);
    }
}
