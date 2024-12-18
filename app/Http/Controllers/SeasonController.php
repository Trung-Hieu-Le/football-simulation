<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SeasonController extends Controller
{
    // Danh sách seasons
    public function index()
    {
        $seasons = DB::table('seasons')->get();
        return view('seasons.index', compact('seasons'));
    }

    // Xóa season
    public function destroy($id)
    {

        DB::table('histories')->where('season_id', $id)->delete();
        DB::table('matches')->where('season_id', $id)->delete();
        DB::table('team_groups')->where('season_id', $id)->delete();
        DB::table('seasons')->where('id', $id)->delete();
        return redirect()->route('seasons.index')->with('success', 'Season deleted successfully.');
    }

    // Hiển thị form tạo mới
    public function create()
    {
        return view('seasons.create');
    }


    // Hiển thị chi tiết season
    public function show($id)
    {
        $season = DB::table('seasons')->where('id', $id)->first();

        // Lấy thông tin bảng xếp hạng từ histories
        $groupStandings = DB::table('histories')
            ->select('histories.*', 'teams.name as team_name')
            ->join('teams', 'teams.id', 'histories.team_id')
            ->where('season_id', $id)
            ->orderBy('position', 'asc')
            ->get()
            ->groupBy('tier');

        // Lấy thông tin các trận đấu đã xảy ra và sắp tới
        $completedMatches = DB::table('matches')
            ->join('teams as t1', 'matches.team1_id', '=', 't1.id')
            ->join('teams as t2', 'matches.team2_id', '=', 't2.id')
            ->where('matches.team1_score', '!=', null)
            ->where('matches.team2_score', '!=', null)
            ->where('matches.season_id', $id)
            ->select('matches.*', 't1.name as team1_name', 't2.name as team2_name')
            ->get();

        // Lọc các trận đấu sắp tới
        $nextMatches = DB::table('matches')
            ->join('teams as t1', 'matches.team1_id', '=', 't1.id')
            ->join('teams as t2', 'matches.team2_id', '=', 't2.id')
            ->where(function ($query) {
                $query->whereNull('matches.team1_score')
                    ->orWhereNull('matches.team2_score');
            })
            ->where('matches.season_id', $id)
            ->select('matches.*', 't1.name as team1_name', 't2.name as team2_name')
            ->get();


        return view('seasons.show', compact('season', 'groupStandings', 'completedMatches', 'nextMatches'));
    }

    // Lưu season và phân chia teams
    public function store(Request $request)
    {
        if ($request->teams_count % 12 !== 0) {
            return redirect()->back()->withErrors(['teams_count' => 'The number of teams must be divisible by 12.']);
        }

        $seasonId = DB::table('seasons')->insertGetId([
            'season' => $request->season,
            'teams_count' => $request->teams_count
        ]);

        $this->assignTeamsToTiers($seasonId, $request->teams_count);
        $this->createHistories($seasonId, $request->teams_count);
        $this->createMatches($seasonId);

        return redirect()->route('seasons.index')->with('success', 'Season created successfully.');
    }

    // Phân chia teams thành tiers
    private function assignTeamsToTiers($seasonId, $teamsCount)
    {
        $teams = DB::table('teams')->orderBy('id')->take($teamsCount)->get();

        $teamsPerTier = $teamsCount / 3;
        $upDownCount = $teamsPerTier / 4; // Số đội lên/xuống hạng

        $tiers = [
            'tier1' => $teams->slice(0, $teamsPerTier),
            'tier2' => $teams->slice($teamsPerTier, $teamsPerTier),
            'tier3' => $teams->slice($teamsPerTier * 2, $teamsPerTier),
        ];

        foreach ($tiers as $tierName => $tierTeams) {
            DB::table('team_groups')->updateOrInsert([
                'season_id' => $seasonId,
                'tier' => $tierName,
                'team_ids' => $tierTeams->pluck('id')->implode(','),
                'created_at' => now(),
            ]);
        }
    }

    // Tạo histories cho từng đội
    private function createHistories($seasonId, $teamsCount)
    {
        $teams = DB::table('teams')->orderBy('id')->take($teamsCount)->get();

        foreach ($teams as $team) {
            DB::table('histories')->insert([
                'season_id' => $seasonId,
                'team_id' => $team->id,
                'tier' => $this->getTeamTier($team->id, $seasonId),
                'match_played' => 0,
                'goal_scored' => 0,
                'goal_conceded' => 0,
                'goal_difference' => 0,
                'points' => 0,
                'position' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function getTeamTier($teamId, $seasonId)
    {
        $group = DB::table('team_groups')
            ->where('season_id', $seasonId)
            ->whereRaw("FIND_IN_SET($teamId, team_ids) > 0")
            ->first();

        return $group ? $group->tier : 'tier1';
    }

    // Tạo lịch thi đấu Round Robin
    private function createMatches($seasonId)
    {
        // Lấy tất cả các nhóm (tier) trong mùa giải
        $teamGroups = DB::table('team_groups')->where('season_id', $seasonId)->get();

        // Khởi tạo mảng chứa các trận đấu
        $matches = [];

        // Duyệt qua từng nhóm (tier) và xử lý theo tier
        foreach ($teamGroups as $group) {
            $teamIds = explode(',', $group->team_ids);

            // Đảo ngược thứ tự đội trong nhóm (tier)
            $teamIds = array_reverse($teamIds);
            // Tạo lịch thi đấu Round Robin cho mỗi nhóm đội
            $groupMatches = $this->generateGroupMatches($seasonId, $teamIds, $group->tier);
            // dd($groupMatches);
            // Thêm các trận đấu vào mảng matches
            $matches = array_merge($matches, $groupMatches);
        }

        // Chèn tất cả các trận đấu vào bảng 'matches'
        DB::table('matches')->insert($matches);
    }


    private function generateGroupMatches($season_id, $team_ids, $group_name)
{
    $numTeams = count($team_ids);

    if ($numTeams % 2 !== 0) {
        $team_ids[] = null; // null đại diện cho "bye"
        $numTeams++;
    }

    $rounds = $numTeams - 1; // Số vòng đấu
    $matches = [];

    // Tạo các trận đấu theo vòng
    for ($round = 0; $round < $rounds; $round++) {
        for ($i = 0; $i < $numTeams / 2; $i++) {
            $team1 = $team_ids[$i];
            $team2 = $team_ids[$numTeams - 1 - $i];

            // Kiểm tra và tạo trận đấu nếu không phải "bye"
            if ($team1 !== null && $team2 !== null) {
                $matches[] = [
                    'season_id' => $season_id,
                    'tier' => $group_name,
                    'round' => $round + 1,  // Số vòng bắt đầu từ 1
                    'team1_id' => $team1,
                    'team2_id' => $team2,
                    'team1_score' => null,
                    'team2_score' => null,
                    'created_at' => now(),
                ];
            }
        }

        // Xoay các đội để tạo lịch thi đấu tiếp theo
        $rotated = [$team_ids[0]]; // Đội đầu tiên giữ nguyên
        $remainingTeams = array_slice($team_ids, 1); // Các đội còn lại

        // Xoay các đội còn lại theo chiều ngược kim đồng hồ
        array_unshift($remainingTeams, array_pop($remainingTeams)); // Di chuyển đội cuối cùng lên đầu

        // Ghép lại đội đầu tiên với các đội đã xoay
        $team_ids = array_merge($rotated, $remainingTeams);
    }

    return $matches;
}

}
