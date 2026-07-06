<?php

namespace App\Models\Cup;

use Illuminate\Database\Eloquent\Model;
use App\Enums\CupSeasonResult;

class Position extends Model
{
    protected $table = 'cup_positions';
    
    protected $fillable = [
        'cup_standing_id',
        'season_id',
        'position',
        'result',
    ];

    protected $attributes = [
        'result' => 'group_stage',
    ];

    protected $casts = [
        'result' => CupSeasonResult::class,
    ];

    public function standing()
    {
        return $this->belongsTo(Standing::class, 'cup_standing_id');
    }

    public function season()
    {
        return $this->belongsTo(Season::class, 'season_id');
    }
}
