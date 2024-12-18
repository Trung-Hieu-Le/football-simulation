<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LeagueController extends Controller
{
    // Hiển thị chi tiết mùa giải
    public function detail($season_id)
    {
        $season = DB::table('seasons')->where('id', $season_id)->first();
        $matches = DB::table('matches')->where('season_id', $season_id)->get();

        $groupMatches = $matches->where('round', 'Group Stage');
        $knockoutMatches = $matches->where('round', '!=', 'Group Stage');

        return view('league.detail', compact('season', 'groupMatches', 'knockoutMatches'));
    }

    // Giả lập tất cả các trận đấu tiếp theo
    public function simulateNextMatches($season_id)
    {
        $season = DB::table('seasons')->where('id', $season_id)->first();
        $matches = DB::table('matches')
            ->where('season_id', $season_id)
            ->whereNull('team1_score')
            ->whereNull('team2_score')
            ->orderBy('id', 'asc')
            ->get();

        foreach ($matches as $match) {
            app(MatchController::class)->simulateMatch($match->id);
        }

        // Sau khi hoàn tất vòng bảng, cập nhật vòng loại trực tiếp
        app(SeasonController::class)->updateKnockoutSchedule($season_id);

        return redirect()->route('league.detail', $season_id)->with('success', 'Đã giả lập các trận đấu tiếp theo.');
    }
}
