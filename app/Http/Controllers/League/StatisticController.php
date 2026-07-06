<?php

namespace App\Http\Controllers\League;

use App\Http\Controllers\Controller;
use App\Models\League\Season;
use App\Models\League\Standing;
use App\Models\League\Match;
use App\Models\Team;
use Illuminate\Support\Facades\DB;

class StatisticController extends Controller
{
    public function index($seasonId)
    {
        $season = Season::findOrFail($seasonId);
        
        $topScorers = $this->getTopScorers($seasonId);
        $topAssists = $this->getTopPossession($seasonId);
        $mostFouls = $this->getMostFouls($seasonId);
        $bestDefense = $this->getBestDefense($seasonId);
        
        return view('league.statistics.index', compact(
            'season',
            'topScorers',
            'topAssists',
            'mostFouls',
            'bestDefense'
        ));
    }

    protected function getTopScorers($seasonId, $limit = 10)
    {
        return Standing::where('season_id', $seasonId)
                      ->with('team')
                      ->orderByDesc('goal_scored')
                      ->orderByDesc('match_played')
                      ->limit($limit)
                      ->get();
    }

    protected function getTopPossession($seasonId, $limit = 10)
    {
        return Standing::where('season_id', $seasonId)
                      ->with('team')
                      ->where('match_played', '>', 0)
                      ->orderByDesc('average_possession')
                      ->limit($limit)
                      ->get();
    }

    protected function getMostFouls($seasonId, $limit = 10)
    {
        return Standing::where('season_id', $seasonId)
                      ->with('team')
                      ->orderByDesc('foul')
                      ->limit($limit)
                      ->get();
    }

    protected function getBestDefense($seasonId, $limit = 10)
    {
        return Standing::where('season_id', $seasonId)
                      ->with('team')
                      ->where('match_played', '>', 0)
                      ->orderBy('goal_conceded')
                      ->orderByDesc('match_played')
                      ->limit($limit)
                      ->get();
    }

    public function allTimeStats()
    {
        $champions = $this->getAllTimeChampions();
        $topElo = $this->getTopEloTeams();
        $mostWins = $this->getMostWins();
        
        return view('league.statistics.all-time', compact(
            'champions',
            'topElo',
            'mostWins'
        ));
    }

    protected function getAllTimeChampions()
    {
        return DB::table('league_positions')
                ->join('league_standings', 'league_positions.league_standing_id', '=', 'league_standings.id')
                ->join('teams', 'league_standings.team_id', '=', 'teams.id')
                ->join('league_seasons', 'league_positions.season_id', '=', 'league_seasons.id')
                ->where('league_positions.result', 'champion')
                ->select(
                    'teams.name',
                    'league_seasons.season',
                    'league_standings.division',
                    'league_standings.points',
                    'league_standings.goal_scored',
                    'league_standings.goal_conceded'
                )
                ->orderByDesc('league_seasons.season')
                ->get();
    }

    protected function getTopEloTeams($limit = 20)
    {
        return Team::orderByDesc('elo')
                  ->limit($limit)
                  ->get();
    }

    protected function getMostWins($limit = 20)
    {
        return Standing::select(
                    'team_id',
                    DB::raw('SUM(win) as total_wins'),
                    DB::raw('SUM(match_played) as total_matches'),
                    DB::raw('SUM(goal_scored) as total_goals')
                )
                ->groupBy('team_id')
                ->with('team')
                ->orderByDesc('total_wins')
                ->limit($limit)
                ->get();
    }
}
