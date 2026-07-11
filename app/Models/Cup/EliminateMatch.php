<?php

namespace App\Models\Cup;

use App\Models\Concerns\HasMatchEvents;
use Illuminate\Database\Eloquent\Model;
use App\Models\Team;

class EliminateMatch extends Model
{
    use HasMatchEvents;

    protected $table = 'cup_eliminate_stage_matches';

    protected $fillable = [
        'season_id',
        'round',
        'branch',
        'slot_index',
        'team1_id',
        'team2_id',
        'team1_score',
        'team2_score',
        'team1_possession',
        'team2_possession',
        'team1_foul',
        'team2_foul',
        'winner_id',
        'match_events',
    ];

    protected $casts = [
        'match_events' => 'array',
    ];

    protected $attributes = [
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

    public function winner()
    {
        return $this->belongsTo(Team::class, 'winner_id');
    }

    public function isPlayed()
    {
        return $this->winner_id !== null;
    }

    public function getLoserId()
    {
        if (!$this->isPlayed()) {
            return null;
        }

        return $this->winner_id === $this->team1_id ? $this->team2_id : $this->team1_id;
    }
}
