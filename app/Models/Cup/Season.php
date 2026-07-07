<?php

namespace App\Models\Cup;

use Illuminate\Database\Eloquent\Model;
use App\Models\Team;

class Season extends Model
{
    protected $table = 'cup_seasons';
    
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

    public function groupStageMatches()
    {
        return $this->hasMany(GroupStageMatch::class, 'season_id');
    }

    public function eliminateMatches()
    {
        return $this->hasMany(EliminateMatch::class, 'season_id');
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
