<?php

namespace App\Http\Controllers;

use App\Models\League\Season as LeagueSeason;
use App\Models\Cup\Season as CupSeason;
use App\Models\Team;
use App\Services\StatisticsService;

class HomeController extends Controller
{
    public function __construct(protected StatisticsService $statisticsService)
    {
    }

    public function index()
    {
        $latestLeague = LeagueSeason::orderBy('season', 'desc')->first();
        $latestCup = CupSeason::orderBy('season', 'desc')->first();
        $topEloTeams = Team::orderByDesc('elo')->limit(10)->get();

        $leagueChampion = $latestLeague
            ? $this->statisticsService->getLeagueChampionForSeason($latestLeague->id)
            : null;
        $cupChampion = $latestCup
            ? $this->statisticsService->getCupChampionForSeason($latestCup->id)
            : null;

        return view('home', compact(
            'latestLeague',
            'latestCup',
            'topEloTeams',
            'leagueChampion',
            'cupChampion'
        ));
    }

    public function selectMode()
    {
        return view('select-mode');
    }
}
