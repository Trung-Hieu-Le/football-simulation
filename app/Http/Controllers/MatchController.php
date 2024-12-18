<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MatchController extends Controller
{
    // Giả lập kết quả của một trận đấu
    public function simulateMatch($matchId)
    {
        $match = DB::table('matches')->where('id', $matchId)->first();

        $team1Goals = rand(0, 5); // Giả lập số bàn thắng đội 1
        $team2Goals = rand(0, 5); // Giả lập số bàn thắng đội 2

        DB::table('matches')->where('id', $matchId)->update([
            'team1_score' => $team1Goals,
            'team2_score' => $team2Goals,
            'updated_at' => now(),
        ]);

        // Cập nhật lịch sử đội
        $this->updateHistory($match->team1_id, $match->season_id, $team1Goals, $team2Goals);
        $this->updateHistory($match->team2_id, $match->season_id, $team2Goals, $team1Goals);
    }

    // Cập nhật lịch sử đội bóng
    private function updateHistory($teamId, $seasonId, $goalsScored, $goalsConceded)
    {
        if (!$teamId) return;

        DB::table('histories')->updateOrInsert(
            ['team_id' => $teamId, 'season_id' => $seasonId],
            [
                'matches_played' => DB::raw('matches_played + 1'),
                'goal_scored' => DB::raw("goals_scored + $goalsScored"),
                'goal_conceded' => DB::raw("goals_conceded + $goalsConceded"),
                'updated_at' => now(),
            ]
        );
    }
}
