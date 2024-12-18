<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LeagueController extends Controller
{
    public function detail($season_id)
    {
        // Lấy thông tin mùa giải
        $season = DB::table('seasons')->where('id', $season_id)->first();

        // Lấy danh sách các nhóm và đội trong mỗi nhóm
        $groups = DB::table('team_groups')
            ->where('season_id', $season_id)
            ->select('group_name', 'team_ids') // `team_ids` lưu dưới dạng JSON
            ->get();

        $groupStandings = [];
        foreach ($groups as $group) {
            $team_ids = json_decode($group->team_ids); // Giải mã danh sách đội từ JSON

            // Lấy thông tin vị trí của từng đội trong bảng
            $standings = DB::table('histories')
                ->join('teams', 'histories.team_id', '=', 'teams.id')
                ->whereIn('team_id', $team_ids)
                ->where('season_id', $season_id)
                ->select('team_id', 'teams.name as team_name', 'teams.color_1', 'teams.color_2', 'teams.color_3', 'position', 'match_played', 'goal_scored', 'goal_conceded', 'goal_difference', 'tier', 'points')
                ->orderBy('position', 'asc')
                ->get();

            $groupStandings[$group->group_name] = $standings;
        }

        // Lấy danh sách các trận đấu và phân loại theo vòng
        $matches = DB::table('matches')
            ->where('season_id', $season_id)
            ->orderBy('id', 'asc')
            ->get()
            ->groupBy('round');
        $nextMatch = DB::table('matches')
            ->leftJoin('teams as team1', 'matches.team1_id', '=', 'team1.id')
            ->leftJoin('teams as team2', 'matches.team2_id', '=', 'team2.id')
            ->where('season_id', $season_id)
            ->whereNull('team1_score') // Các trận chưa có tỉ số
            ->whereNull('team2_score')
            ->select(
                'matches.round',
                'matches.team1_id',
                'matches.team2_id',
                'team1.name as team1_name',
                'team1.color_1 as team1_color_1',
                'team1.color_2 as team1_color_2',
                'team1.color_3 as team1_color_3',
                'team2.name as team2_name',
                'team2.color_1 as team2_color_1',
                'team2.color_2 as team2_color_2',
                'team2.color_3 as team2_color_3'
            )
            ->limit(8) // Lấy 8 trận tiếp theo
            ->get();
        // dd($nextMatch, $groupStandings);
        return view('league.detail', compact('season', 'groupStandings', 'matches', 'nextMatch', 'season_id'));
    }

}
