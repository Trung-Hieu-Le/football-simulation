<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SeasonController extends Controller
{
    public function index()
    {
        $seasons = DB::table('seasons')->get();
        return view('seasons.index', compact('seasons'));
    }
    // Tạo mùa giải mới
    public function create()
    {
        return view('seasons.create');
    }

    public function store(Request $request)
    {
        $lastSeason = DB::table('seasons')->max('season') ?? 0;

        // Tự động thêm mùa giải mới với season tăng +1
        DB::table('seasons')->insert([
            'season' => $lastSeason + 1,
            'created_at' => now(),
        ]);
        return redirect()->route('seasons.index')->with('success', 'Mùa giải đã được thêm!');
    }

    // Xóa mùa giải
    public function destroy($id)
    {
        DB::table('team_groups')->where('season_id', $id)->delete();
        DB::table('matches')->where('season_id', $id)->delete();
        DB::table('histories')->where('season_id', $id)->delete();
        DB::table('seasons')->where('id', $id)->delete();
        return redirect()->route('seasons.index');
    }

    //TODO: đếm xem đủ 64 team không, đã chia bảng chưa
    public function groupStage($season_id)
    {
        $existingGroupsCount = DB::table('team_groups')
            ->where('season_id', $season_id)
            ->count();

        if ($existingGroupsCount != 8) {
            DB::table('team_groups')->where('season_id', $season_id)->delete();
            $teams = DB::table('teams')->orderBy('form', 'desc')->orderBy('id')->get();
            $groupsData = array_fill(0, 8, []);
            foreach ($teams as $team) {
                $groupIndex = $this->findGroupForTeam($groupsData, $team);
                $groupsData[$groupIndex][] = $team;
            }

            foreach ($groupsData as $index => $group) {
                $teamIds = collect($group)->pluck('id')->toArray();
                $groupName = chr(65 + $index);
                DB::table('team_groups')->insert([
                    'season_id' => $season_id,
                    'group_name' => $groupName,
                    'team_ids' => json_encode($teamIds),
                    'created_at' => now(),
                ]);
                $this->createInitialHistories($season_id, $teamIds, $groupName);
            }



            return redirect()->back()->with('success', 'Đã chia bảng lại từ đầu!');
        }

        return redirect()->back()->with('error', 'Mùa giải này đã có đủ 8 bảng!');
    }


    private function findGroupForTeam(&$groupsData, $team)
    {
        $availableGroups = [];

        foreach ($groupsData as $index => $group) {
            if (count($group) >= 8) {
                continue;
            }

            $regionCount = collect($group)->where('region', $team->region)->count();
            $teamCount = count($group);
            if ($regionCount < 5) {
                $availableGroups[] = ['index' => $index, 'regionCount' => $regionCount, 'teamCount' => $teamCount];
            }
        }

        if (!empty($availableGroups)) {
            usort($availableGroups, function ($a, $b) {
                return $a['regionCount'] <=> $b['regionCount'] ?: $a['teamCount'] <=> $b['teamCount'];
            });

            return $availableGroups[0]['index'];
        }

        $leastPopulatedGroup = collect($groupsData)
            ->map(fn($group, $index) => ['index' => $index, 'count' => count($group)])
            ->filter(fn($group) => $group['count'] < 8)
            ->sortBy('count')
            ->first();

        return $leastPopulatedGroup['index'];
    }

    public function generateSchedule($season_id)
    {
        $groups = DB::table('team_groups')
            ->where('season_id', $season_id)
            ->select('group_name', 'team_ids')
            ->get();

        $groupMatches = [];

        foreach ($groups as $index => $group) {
            $team_ids = json_decode($group->team_ids);
            if (($index + 1) % 2 == 0) {
                $team_ids = array_reverse($team_ids);
            }

            $groupMatches[$group->group_name] = $this->generateGroupMatches($season_id, $team_ids);
        }

        $schedule = [];
        $maxRounds = max(array_map('count', $groupMatches));
        for ($round = 0; $round < $maxRounds; $round++) {
            foreach ($groupMatches as $matches) {
                if (isset($matches[$round])) {
                    $schedule[] = $matches[$round];
                }
            }
        }
        // dd($schedule);
        foreach ($schedule as $match) {
            DB::table('matches')->insert($match);
        }

        return redirect()->back()->with('success', 'Lịch đấu vòng bảng đã được tạo thành công!');
    }

    private function generateGroupMatches($season_id, $team_ids)
    {
        $numTeams = count($team_ids);

        if ($numTeams % 2 !== 0) {
            $team_ids[] = null; // null đại diện cho "bye"
            $numTeams++;
        }

        $rounds = $numTeams - 1; // Số vòng đấu
        $matches = [];

        for ($round = 0; $round < $rounds; $round++) {
            for ($i = 0; $i < $numTeams / 2; $i++) {
                $team1 = $team_ids[$i];
                $team2 = $team_ids[$numTeams - 1 - $i];

                if ($team1 !== null && $team2 !== null) {
                    $matches[] = [
                        'season_id' => $season_id,
                        'round' => 'group_stage',
                        'team1_id' => $team1,
                        'team2_id' => $team2,
                        'team1_score' => null,
                        'team2_score' => null,
                        'created_at' => now(),
                    ];
                }
            }
            $rotated = [$team_ids[0]];
            for ($j = 1; $j < $numTeams; $j++) {
                $rotated[] = $team_ids[($j - 1 + $numTeams - 1) % ($numTeams - 1) + 1];
            }
            $team_ids = $rotated;
        }

        return $matches;
    }

    //TODO: check
    private function createInitialHistories($season_id, $team_ids, $groupName)
    {
        // Kiểm tra và xóa lịch sử của các đội bóng cho mùa giải này nếu có
        DB::table('histories')->where('season_id', $season_id)->delete();
        
        // Chỉ tạo lịch sử nếu có đủ 64 đội
        if (count($team_ids) == 8) {
            foreach ($team_ids as $team_id) {
                DB::table('histories')->insert([
                    'team_id' => $team_id,
                    'season_id' => $season_id,
                    'group' => $groupName,
                    'match_played' => 0,
                    'goal_scored' => 0,
                    'goal_conceded' => 0,
                    'goal_difference' => 0,
                    'position' => 0, // Vị trí sẽ được cập nhật sau khi giả lập các trận
                    'tier' => 'group_stage', // Hoặc các cấp khác nếu có
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }


    private function generateKnockoutSchedule($season_id)
    {
        // (Code sinh lịch vòng loại trực tiếp giống như đã trình bày ở trên)
    }

    // Cập nhật lịch vòng loại trực tiếp sau khi kết thúc vòng bảng
    public function updateKnockoutSchedule($season_id)
    {
        // (Code cập nhật vòng loại trực tiếp như đã trình bày ở trên)
    }
}
