<?php

namespace App\Http\Controllers;

use App\Services\StatisticsService;

class StatisticController extends Controller
{
    public function __construct(protected StatisticsService $statisticsService)
    {
    }

    public function allTime()
    {
        return view('statistics.all-time', [
            'leagueChampions' => $this->statisticsService->getLeagueChampions(),
            'cupChampions' => $this->statisticsService->getCupChampions(),
            'mostWins' => $this->statisticsService->getCombinedMostWins(),
        ]);
    }
}
