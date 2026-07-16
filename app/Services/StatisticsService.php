<?php

namespace App\Services;

use App\Enums\DivisionLevel;
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
        $fromPositions = DB::table('league_positions')
            ->join('league_standings', 'league_positions.league_standing_id', '=', 'league_standings.id')
            ->join('teams', 'league_standings.team_id', '=', 'teams.id')
            ->join('league_seasons', 'league_positions.season_id', '=', 'league_seasons.id')
            ->where('league_positions.result', 'champion')
            ->where('league_standings.division', DivisionLevel::DIVISION1->value)
            ->select(
                'league_seasons.id as season_id',
                'teams.id as team_id',
                'teams.name',
                'teams.color_1',
                'teams.color_2',
                'teams.color_3',
                'teams.shirt_type',
                'league_seasons.season',
                'league_standings.points',
                'league_standings.goal_scored',
                'league_standings.goal_conceded'
            )
            ->orderByDesc('league_seasons.season')
            ->get();

        $coveredSeasonIds = $fromPositions->pluck('season_id')->unique();

        $fallback = $this->getLeagueChampionsFromStandings(
            $coveredSeasonIds->isEmpty() ? null : $coveredSeasonIds->all()
        );

        return $fromPositions->concat($fallback)->sortByDesc('season')->values();
    }

    protected function getLeagueChampionsFromStandings(?array $excludeSeasonIds = null): Collection
    {
        $query = DB::table('league_standings')
            ->join('teams', 'league_standings.team_id', '=', 'teams.id')
            ->join('league_seasons', 'league_standings.season_id', '=', 'league_seasons.id')
            ->where('league_standings.division', DivisionLevel::DIVISION1->value)
            ->where('league_standings.match_played', '>', 0)
            ->select(
                'league_seasons.id as season_id',
                'teams.id as team_id',
                'teams.name',
                'league_seasons.season',
                'league_standings.points',
                'league_standings.goal_scored',
                'league_standings.goal_conceded',
                'league_standings.goal_difference'
            );

        if ($excludeSeasonIds) {
            $query->whereNotIn('league_seasons.id', $excludeSeasonIds);
        }

        $standings = $query->get();

        return $standings
            ->groupBy('season_id')
            ->map(fn ($rows) => $this->pickTopStanding($rows))
            ->values();
    }

    protected function pickTopStanding(Collection $rows): object
    {
        return $rows->sortByDesc('goal_scored')
            ->sortByDesc('goal_difference')
            ->sortByDesc('points')
            ->first();
    }

    public function getCupChampions(): Collection
    {
        return DB::table('cup_positions')
            ->join('cup_standings', 'cup_positions.cup_standing_id', '=', 'cup_standings.id')
            ->join('teams', 'cup_standings.team_id', '=', 'teams.id')
            ->join('cup_seasons', 'cup_positions.season_id', '=', 'cup_seasons.id')
            ->where('cup_positions.result', 'champion')
            ->select(
                'teams.id as team_id',
                'teams.name',
                'teams.color_1',
                'teams.color_2',
                'teams.color_3',
                'teams.shirt_type',
                'cup_seasons.season',
                'cup_standings.points',
                'cup_standings.goal_scored',
                'cup_standings.goal_conceded'
            )
            ->orderByDesc('cup_seasons.season')
            ->get();
    }

    public function getLeagueChampionForSeason(int $seasonId): ?object
    {
        return $this->getLeagueChampionsForSeasonIds([$seasonId])->get($seasonId);
    }

    public function getCupChampionForSeason(int $seasonId): ?object
    {
        return $this->getCupChampionsForSeasonIds([$seasonId])->get($seasonId);
    }

    public function getLeagueChampionsForSeasonIds(array $seasonIds): Collection
    {
        if (empty($seasonIds)) {
            return collect();
        }

        $fromPositions = DB::table('league_positions')
            ->join('league_standings', 'league_positions.league_standing_id', '=', 'league_standings.id')
            ->join('teams', 'league_standings.team_id', '=', 'teams.id')
            ->whereIn('league_positions.season_id', $seasonIds)
            ->where('league_positions.result', 'champion')
            ->where('league_standings.division', DivisionLevel::DIVISION1->value)
            ->select(
                'league_positions.season_id',
                'teams.id as team_id',
                'teams.name',
                'teams.color_1',
                'teams.color_2',
                'teams.color_3',
                'teams.shirt_type',
                'league_standings.points'
            )
            ->get()
            ->keyBy('season_id');

        $missingIds = collect($seasonIds)->diff($fromPositions->keys());

        if ($missingIds->isEmpty()) {
            return $fromPositions;
        }

        $fallback = DB::table('league_standings')
            ->join('teams', 'league_standings.team_id', '=', 'teams.id')
            ->whereIn('league_standings.season_id', $missingIds)
            ->where('league_standings.division', DivisionLevel::DIVISION1->value)
            ->where('league_standings.match_played', '>', 0)
            ->select(
                'league_standings.season_id',
                'teams.id as team_id',
                'teams.name',
                'teams.color_1',
                'teams.color_2',
                'teams.color_3',
                'teams.shirt_type',
                'league_standings.points',
                'league_standings.goal_difference',
                'league_standings.goal_scored'
            )
            ->get()
            ->groupBy('season_id')
            ->map(fn ($rows) => $this->pickTopStanding($rows));

        return $fromPositions->union($fallback);
    }

    public function getCupChampionsForSeasonIds(array $seasonIds): Collection
    {
        if (empty($seasonIds)) {
            return collect();
        }

        return DB::table('cup_positions')
            ->join('cup_standings', 'cup_positions.cup_standing_id', '=', 'cup_standings.id')
            ->join('teams', 'cup_standings.team_id', '=', 'teams.id')
            ->whereIn('cup_positions.season_id', $seasonIds)
            ->where('cup_positions.result', 'champion')
            ->select(
                'cup_positions.season_id',
                'teams.id as team_id',
                'teams.name',
                'teams.color_1',
                'teams.color_2',
                'teams.color_3',
                'teams.shirt_type',
                'cup_standings.points'
            )
            ->get()
            ->keyBy('season_id');
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
