<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MatchController extends Controller
{
    public function simulateMatch($matchId)
    {
        // Lấy trận đấu
        $match = DB::table('matches')->where('id', $matchId)->first();

        $team1Goals = rand(0, 5); // Giả lập số bàn thắng đội 1
        $team2Goals = rand(0, 5); // Giả lập số bàn thắng đội 2

        DB::table('matches')->where('id', $matchId)->update([
            'team1_score' => $team1Goals,
            'team2_score' => $team2Goals,
            'updated_at' => now(),
        ]);

        $this->updateHistory($match->team1_id, $match->season_id, $team1Goals, $team2Goals);
        $this->updateHistory($match->team2_id, $match->season_id, $team2Goals, $team1Goals);
        $this->updateStandings($match->season_id);
    }

    private function updateHistory($teamId, $seasonId, $goalsScored, $goalsConceded)
    {
        if (!$teamId) return;

        $history = DB::table('histories')
            ->where('team_id', $teamId)
            ->where('season_id', $seasonId)
            ->first();

        $matchPlayed = $history->match_played + 1;
        $goalScored = $history->goal_scored + $goalsScored;
        $goalConceded = $history->goal_conceded + $goalsConceded;
        $goalDifference = $goalScored - $goalConceded;
        $points = $history->points; // lấy điểm số cũ
        if ($goalsScored > $goalsConceded) {
            $points += 3; // thắng 3 điểm
        } elseif ($goalsScored == $goalsConceded) {
            $points += 1; // hòa 1 điểm
        }
        $tier = $this->getTierByMatchPlayed($matchPlayed);

        DB::table('histories')->updateOrInsert(
            ['team_id' => $teamId, 'season_id' => $seasonId],
            [
                'match_played' => $matchPlayed,
                'goal_scored' => $goalScored,
                'goal_conceded' => $goalConceded,
                'goal_difference' => $goalDifference,
                'points' => $points,
                'tier' => $tier,
                'updated_at' => now(),
            ]
        );
    }
    private function updateStandings($season_id)
    {
        // Lấy tất cả lịch sử của các đội trong mùa giải, nhóm theo bảng
        $teamsHistory = DB::table('histories')
            ->where('season_id', $season_id)
            ->get()
            ->groupBy('group');  // Giả sử 'group' là trường xác định bảng của đội

        // Lặp qua các bảng và sắp xếp đội trong mỗi bảng riêng biệt
        foreach ($teamsHistory as $group => $groupTeams) {
            // Sắp xếp các đội trong bảng theo điểm số, hiệu số bàn thắng, bàn thắng, phong độ, và ID đội
            $sortedTeams = $groupTeams->sort(function ($a, $b) {
                return $b->points <=> $a->points
                    ?: $b->goal_difference <=> $a->goal_difference // So sánh hiệu số bàn thắng
                    ?: $b->goal_scored <=> $a->goal_scored // So sánh số bàn thắng
                    ?: strcmp(implode('', array_reverse($b->form)), implode('', array_reverse($a->form))) // So sánh phong độ
                    ?: $a->team_id <=> $b->team_id; // Nếu vẫn bằng nhau, sắp xếp theo ID đội
            });

            // Cập nhật vị trí cho các đội trong bảng
            foreach ($sortedTeams as $index => $team) {
                DB::table('histories')->where('team_id', $team->team_id)
                    ->where('season_id', $season_id)
                    ->where('group', $group)  // Đảm bảo chỉ cập nhật cho đội trong bảng này
                    ->update(['position' => $index + 1]);
            }
        }
    }



    private function getTierByMatchPlayed($matchPlayed)
    {
        if ($matchPlayed <= 7) {
            return 'group_stage'; // Giai đoạn vòng bảng
        } elseif ($matchPlayed == 8) {
            return 'round_of_32'; // Vòng 32 đội
        } elseif ($matchPlayed == 9) {
            return 'round_of_16'; // Vòng 16 đội
        } elseif ($matchPlayed == 10) {
            return 'quarter_final'; // Vòng tứ kết
        } elseif ($matchPlayed == 11) {
            return 'semi_final'; // Vòng bán kết
        } elseif ($matchPlayed == 12) {
            return 'final'; // Chung kết
        } else {
            return 'unknown'; // Trường hợp không xác định
        }
    }
}
