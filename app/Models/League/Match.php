<?php

namespace App\Models\League;

use Illuminate\Database\Eloquent\Model;
use App\Models\Team;

class Match extends Model
{
    protected $table = 'league_matches';
    
    protected $fillable = [
        'season_id',
        'division',
        'round',
        'team1_id',
        'team2_id',
        'team1_score',
        'team2_score',
        'team1_possession',
        'team2_possession',
        'team1_foul',
        'team2_foul',
    ];

    protected $attributes = [
        'team1_score' => 0,
        'team2_score' => 0,
        'team1_possession' => 50,
        'team2_possession' => 50,
        'team1_foul' => 0,
        'team2_foul' => 0,
    ];

    public function season()
    {
        return $this->belongsTo(Season::class, 'season_id');
    }

    public function team1()
    {
        return $this->belongsTo(Team::class, 'team1_id');
    }

    public function team2()
    {
        return $this->belongsTo(Team::class, 'team2_id');
    }

    public function isPlayed()
    {
        return $this->team1_score !== null && $this->team2_score !== null;
    }

    public function getWinnerId()
    {
        if (!$this->isPlayed()) {
            return null;
        }
        
        if ($this->team1_score > $this->team2_score) {
            return $this->team1_id;
        } elseif ($this->team2_score > $this->team1_score) {
            return $this->team2_id;
        }
        
        return null; // Draw
    }
}
