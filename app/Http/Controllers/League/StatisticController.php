<?php

namespace App\Http\Controllers\League;

use App\Http\Controllers\Controller;
use App\Models\League\Season;
use App\Models\League\Standing;
use App\Services\StatisticsService;

class StatisticController extends Controller
{
    public function __construct(protected StatisticsService $statisticsService)
    {
    }

    public function index($seasonId)
    {
        $season = Season::findOrFail($seasonId);
        $stats = $this->statisticsService->getSeasonStats(Standing::class, $seasonId);

        return view('statistics.season', array_merge($stats, [
            'mode' => 'league',
            'season' => $season,
        ]));
    }
}
