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
            ->select('group_name', 'team_ids')  // `team_ids` lưu dưới dạng JSON
            ->get();

        $groupStandings = [];
        foreach ($groups as $group) {
            $team_ids = json_decode($group->team_ids);
            $groupStandings[$group->group_name] = $this->calculateGroupStandings($season_id, $team_ids);
        }

        // Lấy danh sách các trận đấu và phân loại theo vòng
        $matches = DB::table('matches')
            ->where('season_id', $season_id)
            ->orderBy('round', 'asc')
            ->get()
            ->groupBy('round');
        return view('league.detail', compact('season', 'groupStandings', 'matches'));
    }

    private function calculateGroupStandings($season_id, $team_ids)
    {
        $teams = DB::table('teams')->whereIn('id', $team_ids)->get();

        // Tạo bảng xếp hạng ban đầu
        $standings = [];
        foreach ($teams as $team) {
            $standings[$team->id] = [
                'id' => $team->id,
                'name' => $team->name,
                'points' => 0,
                'goal_difference' => 0,
                'goals_scored' => 0,
                'form' => [],
            ];
        }

        // Lấy các trận đấu trong nhóm
        $matches = DB::table('matches')
            ->where('season_id', $season_id)
            ->whereIn('team1_id', $team_ids)
            ->whereIn('team2_id', $team_ids)
            ->whereNotNull('team1_score')
            ->whereNotNull('team2_score')
            ->get();

        // Cập nhật dữ liệu xếp hạng
        foreach ($matches as $match) {
            $team1 = &$standings[$match->team1_id];
            $team2 = &$standings[$match->team2_id];

            $team1['goals_scored'] += $match->team1_score;
            $team2['goals_scored'] += $match->team2_score;

            $goalDifference = $match->team1_score - $match->team2_score;
            $team1['goal_difference'] += $goalDifference;
            $team2['goal_difference'] -= $goalDifference;

            if ($match->team1_score > $match->team2_score) {
                $team1['points'] += 3;
                $team1['form'][] = 'W';
                $team2['form'][] = 'L';
            } elseif ($match->team1_score < $match->team2_score) {
                $team2['points'] += 3;
                $team2['form'][] = 'W';
                $team1['form'][] = 'L';
            } else {
                $team1['points'] += 1;
                $team2['points'] += 1;
                $team1['form'][] = 'D';
                $team2['form'][] = 'D';
            }
        }

        // Sắp xếp bảng xếp hạng
        usort($standings, function ($a, $b) {
            return $b['points'] <=> $a['points']            // Điểm
                ?: $b['goal_difference'] <=> $a['goal_difference'] // Hiệu số bàn thắng
                ?: $b['goals_scored'] <=> $a['goals_scored'] // Tổng số bàn thắng
                ?: strcmp(implode('', array_reverse($b['form'])), implode('', array_reverse($a['form']))) // Phong độ
                ?: $a['id'] <=> $b['id'];                    // ID tăng dần
        });

        return $standings;
    }
}

