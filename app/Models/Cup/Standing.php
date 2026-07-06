<?php

namespace App\Models\Cup;

use Illuminate\Database\Eloquent\Model;
use App\Models\Team;

class Standing extends Model
{
    protected $table = 'cup_standings';
    
    protected $fillable = [
        'team_id',
        'season_id',
        'group',
        'match_played',
        'goal_scored',
        'goal_conceded',
        'goal_difference',
        'average_possession',
        'foul',
        'points',
        'win',
        'draw',
        'lose',
    ];

    protected $attributes = [
        'match_played' => 0,
        'goal_scored' => 0,
        'goal_conceded' => 0,
        'goal_difference' => 0,
        'average_possession' => 50,
        'foul' => 0,
        'points' => 0,
        'win' => 0,
        'draw' => 0,
        'lose' => 0,
    ];

    public function season()
    {
        return $this->belongsTo(Season::class, 'season_id');
    }

    public function team()
    {
        return $this->belongsTo(Team::class, 'team_id');
    }

    public function position()
    {
        return $this->hasOne(Position::class, 'cup_standing_id');
    }

    public function updateFromMatch($goalScored, $goalConceded, $possession, $fouls, $result)
    {
        $this->match_played++;
        $this->goal_scored += $goalScored;
        $this->goal_conceded += $goalConceded;
        $this->goal_difference = $this->goal_scored - $this->goal_conceded;
        $this->foul += $fouls;
        
        $totalPossession = ($this->average_possession * ($this->match_played - 1)) + $possession;
        $this->average_possession = $totalPossession / $this->match_played;
        
        if ($result === 'win') {
            $this->win++;
            $this->points += 3;
        } elseif ($result === 'draw') {
            $this->draw++;
            $this->points += 1;
        } else {
            $this->lose++;
        }
        
        $this->save();
    }
}
