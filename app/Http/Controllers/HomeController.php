<?php

namespace App\Http\Controllers;

use App\Models\League\Season as LeagueSeason;
use App\Models\Cup\Season as CupSeason;
use App\Models\Team;

class HomeController extends Controller
{
    public function index()
    {
        $latestLeague = LeagueSeason::orderBy('season', 'desc')->first();
        $latestCup = CupSeason::orderBy('season', 'desc')->first();
        $topEloTeams = Team::orderByDesc('elo')->limit(10)->get();
        
        return view('home', compact('latestLeague', 'latestCup', 'topEloTeams'));
    }

    public function selectMode()
    {
        return view('select-mode');
    }
}
