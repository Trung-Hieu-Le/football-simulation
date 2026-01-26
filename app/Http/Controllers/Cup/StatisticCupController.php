<?php

namespace App\Http\Controllers\Cup;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class StatisticCupController extends Controller
{
    public function index()
    {
        // 1. Top 4 teams by points (All seasons, Tier 1)
        $topTeams = DB::table('cup_standings')
            ->whereIn('title', ['champion', 'runner_up', '3rd_place', '4th_place']) 
            ->select('season_id', 'team_id', 'position', 'points')
            ->join('teams', 'teams.id', '=', 'cup_standings.team_id')
            ->join('cup_seasons', 'cup_seasons.id', '=', 'cup_standings.season_id')
            ->selectRaw('cup_seasons.season as season, teams.name as team_name, cup_standings.position, cup_standings.points')
            ->groupBy('season_id', 'position', 'cup_standings.team_id', 'points', 'cup_seasons.season', 'teams.name')
            ->orderBy('season_id', 'desc')
            ->get()
            ->groupBy('season');

        // 2. Teams with the most championships (Position 1, Tier 1)
        $champions = DB::table('teams')
            ->leftJoin('cup_standings', 'teams.id', '=', 'cup_standings.team_id')
            ->where('cup_standings.title', 'champion')
            ->select('teams.name', 'teams.region', DB::raw('COUNT(cup_standings.id) as championships'))
            ->groupBy('teams.name', 'teams.region')
            ->orderByDesc('championships')
            ->orderBy('teams.region')
            ->get();

        // Join regions for champions
        $champions = $champions->map(function ($team) {
            $region = DB::table('regions')->where('id', $team->region)->first();
            $team->region_name = $region->name ?? 'Unknown';
            return $team;
        })->sortByDesc('region_name');

        // 3. Additional statistics
        $statistics = [
            // Top 5 Teams with Highest Points
            'highest_points' => DB::table('cup_standings')
                ->join('teams', 'teams.id', '=', 'cup_standings.team_id')
                ->where('title', 'champion')
                ->select('teams.name as team_name', 'cup_standings.points')
                ->orderByDesc('points')
                ->take(5)
                ->get(),
        
            // Top 5 Teams with Lowest Points
            'lowest_points' => DB::table('cup_standings')
                ->join('teams', 'teams.id', '=', 'cup_standings.team_id')
                ->where('title', 'champion')
                ->select('teams.name as team_name', 'cup_standings.points')
                ->orderBy('points')
                ->take(5)
                ->get(),
        
            // Largest Point Gap between 1st and 2nd place
            'largest_gap' => DB::table('cup_standings as h1')
                ->join('cup_standings as h2', function ($join) {
                    $join->on('h1.season_id', '=', 'h2.season_id')
                        ->where('h1.title', '=', 'champion')
                        ->where('h2.title', '=', 'runner_up');
                })
                ->join('teams as t1', 't1.id', '=', 'h1.team_id')
                ->join('teams as t2', 't2.id', '=', 'h2.team_id')
                ->selectRaw('MAX(h1.points - h2.points) as gap, t1.name as team1_name, t2.name as team2_name')
                ->groupBy('t1.name', 't2.name')
                ->take(5)
                ->get(),
        
            // Smallest Point Gap between 1st and 2nd place
            'smallest_gap' => DB::table('cup_standings as h1')
                ->join('cup_standings as h2', function ($join) {
                    $join->on('h1.season_id', '=', 'h2.season_id')
                        ->where('h1.title', '=', 'champion')
                        ->where('h2.title', '=', 'runner_up');
                })
                ->join('teams as t1', 't1.id', '=', 'h1.team_id')
                ->join('teams as t2', 't2.id', '=', 'h2.team_id')
                ->selectRaw('MIN(h1.points - h2.points) as gap, t1.name as team1_name, t2.name as team2_name')
                ->groupBy('t1.name', 't2.name')
                ->take(5)
                ->get(),
        
            // Team with Most Top 4 Appearances
            'most_top4_appearances' => DB::table('cup_standings')
                ->join('teams', 'teams.id', '=', 'cup_standings.team_id')
                ->whereIn('title', ['champion', 'runner_up', '3rd_place', '4th_place']) 
                ->select('teams.name as team_name', DB::raw('COUNT(*) as top4_count'))
                ->groupBy('teams.name')
                ->orderByDesc('top4_count')
                ->take(5)
                ->get(),
        ];
        

        // 4. Top 5 Teams (Win, Draw, Lose)
        $topWinTeams = DB::table('cup_standings')
            ->join('teams', 'cup_standings.team_id', '=', 'teams.id')
            ->select('teams.name', DB::raw('SUM(cup_standings.win) as total_win'))
            ->groupBy('teams.name')
            ->orderByDesc('total_win')
            ->take(5)
            ->get();

        $topDrawTeams = DB::table('cup_standings')
            ->join('teams', 'cup_standings.team_id', '=', 'teams.id')
            ->select('teams.name', DB::raw('SUM(cup_standings.draw) as total_draw'))
            ->groupBy('teams.name')
            ->orderByDesc('total_draw')
            ->take(5)
            ->get();

        $topLoseTeams = DB::table('cup_standings')
            ->join('teams', 'cup_standings.team_id', '=', 'teams.id')
            ->select('teams.name', DB::raw('SUM(cup_standings.lose) as total_lose'))
            ->groupBy('teams.name')
            ->orderByDesc('total_lose')
            ->take(5)
            ->get();

        // 5. Top 5 Teams with highest stats but never won a championship
        $highestStatsNoChampion = DB::table('teams')
            ->join('cup_standings', 'teams.id', '=', 'cup_standings.team_id')
            ->select('teams.name', DB::raw('SUM(DISTINCT (teams.attack + teams.defense + teams.control + teams.stamina + teams.pass + teams.speed + teams.mental + teams.discipline)) as total_stats'))
            ->whereNotIn('teams.id', function ($query) {
                $query->select('team_id')->from('cup_standings')->where('title', 'champion');
            })
            ->groupBy('teams.name')
            ->orderByDesc('total_stats')
            ->take(5)
            ->get();

        // 6. Top 5 Teams with lowest stats that have won a championship
        $lowestStatsChampion = DB::table('teams')
            ->join('cup_standings', 'teams.id', '=', 'cup_standings.team_id')
            ->select('teams.name', DB::raw('SUM(DISTINCT (teams.attack + teams.defense + teams.control + teams.stamina + teams.pass + teams.speed + teams.mental + teams.discipline)) as total_stats'))
            ->whereIn('teams.id', function ($query) {
                $query->select('team_id')->from('cup_standings')->where('title', 'champion');
            })
            ->groupBy('teams.name')
            ->orderBy('total_stats')
            ->take(5)
            ->get();

        return view('cup.statistics.index', compact('topTeams', 'champions', 'statistics', 'topWinTeams', 'topDrawTeams', 'topLoseTeams', 'highestStatsNoChampion', 'lowestStatsChampion'));
    }
}