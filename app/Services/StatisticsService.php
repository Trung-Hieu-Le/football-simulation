<?php

namespace App\Services;

use App\Models\League\Standing as LeagueStanding;
use App\Models\Cup\Standing as CupStanding;
use App\Models\Team;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class StatisticsService
{
    public function getSeasonStats(string $standingModel, int $seasonId, int $limit = 10): array
    {
        return [
            'topScorers' => $standingModel::where('season_id', $seasonId)
                ->with('team')
                ->orderByDesc('goal_scored')
                ->orderByDesc('match_played')
                ->limit($limit)
                ->get(),
            'topPossession' => $standingModel::where('season_id', $seasonId)
                ->with('team')
                ->where('match_played', '>', 0)
                ->orderByDesc('average_possession')
                ->limit($limit)
                ->get(),
            'mostFouls' => $standingModel::where('season_id', $seasonId)
                ->with('team')
                ->orderByDesc('foul')
                ->limit($limit)
                ->get(),
            'bestDefense' => $standingModel::where('season_id', $seasonId)
                ->with('team')
                ->where('match_played', '>', 0)
                ->orderBy('goal_conceded')
                ->orderByDesc('match_played')
                ->limit($limit)
                ->get(),
        ];
    }

    public function getLeagueChampions(): Collection
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

    public function getCupChampions(): Collection
    {
        return DB::table('cup_positions')
            ->join('cup_standings', 'cup_positions.cup_standing_id', '=', 'cup_standings.id')
            ->join('teams', 'cup_standings.team_id', '=', 'teams.id')
            ->join('cup_seasons', 'cup_positions.season_id', '=', 'cup_seasons.id')
            ->where('cup_positions.result', 'champion')
            ->select(
                'teams.name',
                'cup_seasons.season',
                'cup_standings.points',
                'cup_standings.goal_scored',
                'cup_standings.goal_conceded'
            )
            ->orderByDesc('cup_seasons.season')
            ->get();
    }

    public function getCombinedMostWins(int $limit = 20): Collection
    {
        $leagueWins = LeagueStanding::select(
            'team_id',
            DB::raw('SUM(win) as total_wins'),
            DB::raw('SUM(match_played) as total_matches'),
            DB::raw('SUM(goal_scored) as total_goals')
        )->groupBy('team_id')->get()->keyBy('team_id');

        $cupWins = CupStanding::select(
            'team_id',
            DB::raw('SUM(win) as total_wins'),
            DB::raw('SUM(match_played) as total_matches'),
            DB::raw('SUM(goal_scored) as total_goals')
        )->groupBy('team_id')->get()->keyBy('team_id');

        $teamIds = $leagueWins->keys()->merge($cupWins->keys())->unique();
        $teams = Team::whereIn('id', $teamIds)->get()->keyBy('id');

        return $teamIds->map(function ($teamId) use ($leagueWins, $cupWins, $teams) {
            $league = $leagueWins->get($teamId);
            $cup = $cupWins->get($teamId);

            return (object) [
                'team' => $teams->get($teamId),
                'total_wins' => ($league->total_wins ?? 0) + ($cup->total_wins ?? 0),
                'total_matches' => ($league->total_matches ?? 0) + ($cup->total_matches ?? 0),
                'total_goals' => ($league->total_goals ?? 0) + ($cup->total_goals ?? 0),
            ];
        })
            ->sortByDesc('total_wins')
            ->take($limit)
            ->values();
    }
}
