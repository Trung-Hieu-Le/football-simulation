<?php

namespace App\Models\League;

use Illuminate\Database\Eloquent\Model;
use App\Enums\LeagueSeasonResult;

class Position extends Model
{
    protected $table = 'league_positions';
    
    protected $fillable = [
        'league_standing_id',
        'season_id',
        'position',
        'result',
    ];

    protected $casts = [
        'result' => LeagueSeasonResult::class,
    ];

    public function standing()
    {
        return $this->belongsTo(Standing::class, 'league_standing_id');
    }

    public function season()
    {
        return $this->belongsTo(Season::class, 'season_id');
    }
}
