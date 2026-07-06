<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    protected $table = 'teams';
    
    protected $fillable = [
        'name',
        'color_1',
        'color_2',
        'color_3',
        'attack',
        'defense',
        'control',
        'creative',
        'pace',
        'mental',
        'discipline',
        'luck',
        'stamina',
        'goalkeeping',
        'form',
        'elo',
        'region',
        'shirt_type',
    ];

    protected $attributes = [
        'color_1' => '000000',
        'color_2' => '000000',
        'attack' => 50,
        'defense' => 50,
        'control' => 50,
        'creative' => 50,
        'pace' => 50,
        'mental' => 50,
        'discipline' => 50,
        'luck' => 50,
        'stamina' => 50,
        'goalkeeping' => 50,
        'form' => 50,
        'elo' => 1000,
    ];

    public function region()
    {
        return $this->belongsTo(Region::class, 'region', 'id');
    }

    public function updateForm(bool $won)
    {
        $this->form = max(5, min(100, $this->form + ($won ? 5 : -5)));
        $this->save();
    }

    public function updateElo(int $change)
    {
        $this->elo += $change;
        $this->save();
    }

    public function resetToDefault()
    {
        $this->elo = 1000;
        $this->form = 50;
        $this->save();
    }
}
