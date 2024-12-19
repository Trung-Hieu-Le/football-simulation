<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MatchController extends Controller
{
    public function simulateMatch(Request $request)
    {
        $season_id = $request->input('season_id');
        $nextMatches = DB::table('matches')
            ->where('season_id', $season_id)
            ->whereNull('team1_score')
            ->whereNull('team2_score')
            ->limit(1)
            ->get();
        foreach ($nextMatches as $match) {
            // Tạo kết quả ngẫu nhiên cho trận đấu
            $team1_score = rand(0, 5); // Số bàn thắng ngẫu nhiên từ 0 đến 5
            $team2_score = rand(0, 5);

            // Cập nhật kết quả trận đấu
            DB::table('matches')
                ->where('id', $match->id)
                ->update([
                    'team1_score' => $team1_score,
                    'team2_score' => $team2_score,
                    'updated_at' => now(),
                ]);

            $this->updateHistory($match->team1_id, $match->season_id, $team1_score, $team2_score);
            $this->updateHistory($match->team2_id, $match->season_id, $team2_score, $team1_score);
            $this->updateStandings($match->season_id);
        }
        return redirect()->back()->with('success', 'Next matches simulated successfully!');

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
        // $tier = $this->getTierByMatchPlayed($matchPlayed);

        DB::table('histories')->updateOrInsert(
            ['team_id' => $teamId, 'season_id' => $seasonId],
            [
                'match_played' => $matchPlayed,
                'goal_scored' => $goalScored,
                'goal_conceded' => $goalConceded,
                'goal_difference' => $goalDifference,
                'points' => $points,
                // 'tier' => $tier,
                'updated_at' => now(),
            ]
        );
    }

    private function updateStandings($season_id)
{
    // Lấy lịch sử của các đội trong mùa giải
    $teamsHistory = DB::table('histories')
        ->join('teams', 'histories.team_id', '=', 'teams.id')
        ->where('histories.season_id', $season_id)
        ->select(
            'histories.*',
            DB::raw('COALESCE(teams.form, 0) as team_form') // Chuẩn hóa form
        )
        ->get()
        ->groupBy('tier'); // Nhóm theo bảng

    // Lặp qua từng bảng
    foreach ($teamsHistory as $group => $groupTeams) {
        // Sắp xếp các đội trong bảng
        $sortedTeams = $groupTeams->sortByDesc(function ($team) {
            return [$team->points, $team->goal_difference, $team->goal_scored, $team->team_form];
        })->values(); // Đặt lại khóa để đảm bảo tuần tự

        // Cập nhật vị trí
        foreach ($sortedTeams as $index => $team) {
            DB::table('histories')
                ->where('team_id', $team->team_id)
                ->where('season_id', $season_id)
                ->where('tier', $group)
                ->update(['position' => $index + 1]);
        }
    }
}



    // private function getTierByMatchPlayed($matchPlayed)
    // {
    //     if ($matchPlayed <= 7) {
    //         return 'group_stage'; // Giai đoạn vòng bảng
    //     } elseif ($matchPlayed == 8) {
    //         return 'round_of_32'; // Vòng 32 đội
    //     } elseif ($matchPlayed == 9) {
    //         return 'round_of_16'; // Vòng 16 đội
    //     } elseif ($matchPlayed == 10) {
    //         return 'quarter_final'; // Vòng tứ kết
    //     } elseif ($matchPlayed == 11) {
    //         return 'semi_final'; // Vòng bán kết
    //     } elseif ($matchPlayed == 12) {
    //         return 'final'; // Chung kết
    //     } else {
    //         return 'unknown'; // Trường hợp không xác định
    //     }
    // }
}
