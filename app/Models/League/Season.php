<?php

namespace App\Models\League;

use Illuminate\Database\Eloquent\Model;
use App\Models\Team;

class Season extends Model
{
    protected $table = 'league_seasons';
    
    const UPDATED_AT = null;
    
    protected $fillable = [
        'season',
        'teams_count',
        'meta',
    ];

    public function groupTeams()
    {
        return $this->hasMany(GroupTeam::class, 'season_id');
    }

    public function matches()
    {
        return $this->hasMany(LeagueMatch::class, 'season_id');
    }

    public function standings()
    {
        return $this->hasMany(Standing::class, 'season_id');
    }

    public function positions()
    {
        return $this->hasMany(Position::class, 'season_id');
    }
}
