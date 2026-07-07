<?php

namespace App\Models\Cup;

use Illuminate\Database\Eloquent\Model;
use App\Models\Team;

class GroupTeam extends Model
{
    protected $table = 'cup_group_teams';
    
    protected $fillable = [
        'season_id',
        'group',
        'team_ids',
    ];

    public function season()
    {
        return $this->belongsTo(Season::class, 'season_id');
    }

    public function getTeamIdsArray()
    {
        return json_decode($this->team_ids, true) ?? [];
    }

    public function setTeamIdsArray(array $teamIds)
    {
        $this->team_ids = json_encode($teamIds);
    }

    public function teams()
    {
        $teamIds = $this->getTeamIdsArray();
        return Team::whereIn('id', $teamIds)->get();
    }
}
