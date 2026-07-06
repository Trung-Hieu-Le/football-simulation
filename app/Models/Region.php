<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    protected $table = 'regions';
    
    public $timestamps = false;
    
    protected $fillable = [
        'id',
        'name',
        'shortname',
        'description',
    ];

    public function teams()
    {
        return $this->hasMany(Team::class, 'region', 'id');
    }
}
