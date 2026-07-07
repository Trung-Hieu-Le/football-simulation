<?php

namespace App\Http\Controllers\Cup;

use App\Http\Controllers\Controller;
use App\Models\Cup\Season;
use App\Models\Cup\Standing;
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
            'mode' => 'cup',
            'season' => $season,
        ]));
    }
}
