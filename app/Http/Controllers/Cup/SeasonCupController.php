<?php

namespace App\Http\Controllers\Cup;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class SeasonCupController extends Controller
{
    // Danh sách seasons
    public function index()
    {
        // Lấy danh sách các mùa giải
        $seasons = DB::table('seasons')->orderBy('season', 'desc')->get();

        // Thêm thông tin tỷ lệ trận và vòng vào từng season
        $seasons = $seasons->map(function ($season) {
            // Tổng số trận của mùa giải
            $totalMatches = DB::table('group_stage_matches')
                ->where('season_id', $season->id)
                ->count();

            // Số trận đã có tỉ số
            $completedMatchesCount = DB::table('group_stage_matches')
                ->where('season_id', $season->id)
                ->whereNotNull('team1_score')
                ->whereNotNull('team2_score')
                ->count();

            // Tính tỷ lệ số trận đã có tỉ số
            $matchCompletionRate = $totalMatches > 0 ? ($completedMatchesCount / $totalMatches) * 100 : 0;

            // Xác định round hiện tại và round tối đa
            $currentRound = DB::table('group_stage_matches')
                ->where('season_id', $season->id)
                ->where(function ($query) {
                    $query->whereNull('team1_score')
                        ->orWhereNull('team2_score');
                })
                ->orderBy('round', 'asc')
                ->value('round');

            $maxRound = floor($season->teams_count / 8) - 1;
            $currentRound = $currentRound ?? $maxRound;

            // Tính tỷ lệ vòng hiện tại so với tối đa
            $roundRate = $currentRound > 0 ? ($currentRound / ($maxRound)) * 100 : 0;

            // Gắn thêm thông tin vào season
            $season->match_completion_rate = round($matchCompletionRate, 2);
            $season->round_rate = round($roundRate, 2);
            $season->current_round = $currentRound;
            $season->max_round = $maxRound;

            return $season;
        });

        return view('cup.seasons.index', compact('seasons'));
    }


    // Xóa season
    public function destroy($id)
    {
        DB::table('group_stage_standings')->where('season_id', $id)->delete();
        DB::table('group_stage_matches')->where('season_id', $id)->delete();
        DB::table('group_teams')->where('season_id', $id)->delete();
        DB::table('seasons')->where('id', $id)->delete();
        return redirect()->route('cup.seasons.index')->with('success', 'Season deleted successfully.');
    }

    // Xóa season all
    public function destroyAll()
    {
        DB::table('group_stage_standings')->delete();
        DB::table('group_stage_matches')->delete();
        DB::table('group_teams')->delete();
        DB::table('seasons')->delete();
        return redirect()->route('cup.seasons.index')->with('success', 'Season deleted successfully.');
    }

    // Hiển thị form tạo mới
    public function create()
    {
        $lastSeason = DB::table('seasons')->orderBy('id', 'desc')->first();

        $nextSeason = $lastSeason ? $lastSeason->season + 1 : 1;
        $nextTeamsCount = $lastSeason ? $lastSeason->teams_count : 64;

        return view('cup.seasons.create', compact('nextSeason', 'nextTeamsCount'));
    }



    // Hiển thị chi tiết season
    public function show($id)
    {
        $season = DB::table('seasons')->where('id', $id)->first();

        // Lấy thông tin bảng xếp hạng từ histories
        $groupStandings = DB::table('group_stage_standings')
            ->select('group_stage_standings.*', 'teams.name as team_name', 'teams.color_1', 'teams.color_2', 'teams.color_3')
            ->join('teams', 'teams.id', 'group_stage_standings.team_id')
            ->where('season_id', $id)
            ->orderBy('group', 'asc')
            ->orderBy('position', 'asc')
            ->get()
            ->groupBy('group');

        //TODO: currentRound không vượt quá giá trị quy định
        $currentRound = DB::table('group_stage_matches')
            ->where('season_id', $id)
            ->where(function ($query) {
                $query->whereNull('team1_score')
                    ->orWhereNull('team2_score');
            })
            ->orderBy('round', 'asc')
            ->value('round');

        $maxRound = floor($season->teams_count / 8) - 1;
        $currentRound = $currentRound ?? $maxRound + 1;
        $promotionRelegationCount = floor($season->teams_count - 32);

        // Lấy thông tin các trận đấu đã xảy ra và sắp tới
        $completedMatches = DB::table('group_stage_matches')
            ->join('teams as t1', 'group_stage_matches.team1_id', '=', 't1.id')
            ->join('teams as t2', 'group_stage_matches.team2_id', '=', 't2.id')
            ->where('group_stage_matches.team1_score', '!=', null)
            ->where('group_stage_matches.team2_score', '!=', null)
            ->where('group_stage_matches.round', '=', $currentRound - 1) // Chỉ lấy round trước
            ->where('group_stage_matches.season_id', $id)
            ->select(
                'group_stage_matches.*',
                't1.name as team1_name',
                't2.name as team2_name',
                't1.color_1 as team1_c1',
                't1.color_2 as team1_c2',
                't1.color_3 as team1_c3',
                't2.color_1 as team2_c1',
                't2.color_2 as team2_c2',
                't2.color_3 as team2_c3'
            )
            ->get();

        // Lọc các trận đấu sắp tới
        $nextMatches = DB::table('group_stage_matches')
            ->join('teams as t1', 'group_stage_matches.team1_id', '=', 't1.id')
            ->join('teams as t2', 'group_stage_matches.team2_id', '=', 't2.id')
            ->where('group_stage_matches.round', '=', $currentRound) // Chỉ lấy round hiện tại
            ->where('group_stage_matches.season_id', $id)
            ->select(
                'group_stage_matches.*',
                't1.name as team1_name',
                't2.name as team2_name',
                't1.color_1 as team1_c1',
                't1.color_2 as team1_c2',
                't1.color_3 as team1_c3',
                't2.color_1 as team2_c1',
                't2.color_2 as team2_c2',
                't2.color_3 as team2_c3'
            )
            ->get();


        return view('cup.seasons.show', compact('season', 'groupStandings', 'completedMatches', 'nextMatches', 'currentRound', 'maxRound'));
    }

    public function listMatches(Request $request)
    {
        $seasonId = $request->id;
        $matchesByRound = DB::table('group_stage_matches')->orderBy('round')
            ->join('teams as t1', 'group_stage_matches.team1_id', '=', 't1.id')
            ->join('teams as t2', 'group_stage_matches.team2_id', '=', 't2.id')
            ->select(
                'group_stage_matches.*',
                't1.name as team1_name',
                't2.name as team2_name',
                't1.color_1 as team1_c1',
                't1.color_2 as team1_c2',
                't1.color_3 as team1_c3',
                't2.color_1 as team2_c1',
                't2.color_2 as team2_c2',
                't2.color_3 as team2_c3'
            )
            ->where('season_id', $seasonId)
            ->get()
            ->groupBy('round');
        return view('cup.seasons.matches', compact('matchesByRound', 'seasonId'));
    }
    public function showStatistics(Request $request)
    {
        $sortBy = $request->get('sort_by', 'points');
        $seasonId = $request->id;
        $histories = DB::table('group_stage_standings')->selectRaw("
            team_id,
            SUM(match_played) as matches_played,
            SUM(goal_scored) as goals_scored,
            SUM(goal_conceded) as goals_conceded,
            SUM(goal_scored - goal_conceded) as goal_difference,
            SUM(average_possession) as possession,
            SUM(foul) as fouls,
            SUM(points) as points,
            SUM(win) as wins,
            SUM(draw) as draws,
            SUM(lose) as loses,
            teams.name as team_name, teams.color_1 as team_c1, teams.color_2 as team_c2, teams.color_3 as team_c3
        ")
            ->join('teams', 'group_stage_standings.team_id', '=', 'teams.id')
            ->where('season_id', $seasonId)
            ->groupBy('team_id')
            ->orderBy($sortBy, 'desc')
            ->get();

        return view('cup.seasons.histories', compact('histories', 'sortBy', 'seasonId'));
    }





    // Lưu season và phân chia teams
    public function store(Request $request)
    {
        if ($request->teams_count % 32 !== 0) {
            return redirect()->back()->withErrors(['teams_count' => 'The number of teams must be divisible by 12.']);
        }

        $metaOptions = ['attack', 'defense', 'control', 'aggressive', 'stamina', 'penalty'];
        $meta = $request->meta ?: $metaOptions[array_rand($metaOptions)]; // Nếu không chọn thì lấy random
        $seasonId = DB::table('seasons')->insertGetId([
            'season' => $request->season,
            'teams_count' => $request->teams_count,
            'meta' => $meta,
        ]);

        $this->assignTeamsToGroups($seasonId, $request->teams_count);

        return redirect()->route('cup.seasons.index')->with('success', 'Season created successfully.');
    }

    private function assignTeamsToGroups($seasonId, $teamsCount)
    {
        $idLimit = ($teamsCount == 32) ? 48 : null;

        // Tạo query để lấy danh sách team
        $teams = DB::table('teams')
            ->when($idLimit, function ($query, $idLimit) {
                return $query->where('id', '<=', $idLimit); // Chỉ lấy các team có id <= idLimit nếu được định nghĩa
            })
            ->orderBy('form', 'desc')
            ->orderBy('id')
            ->limit($teamsCount)
            ->get();
        $groupNames = range('A', 'H'); // Danh sách tên bảng
        $groups = [];
        $groupCount = count($groupNames);

        // Phân đội lần lượt vào các bảng
        foreach ($teams as $index => $team) {
            $groupIndex = $index % $groupCount; // Lấy chỉ số bảng theo vòng tròn
            $groupName = $groupNames[$groupIndex];

            if (!isset($groups[$groupName])) {
                $groups[$groupName] = [];
            }

            $groups[$groupName][] = $team->id;
        }

        // Chuẩn bị dữ liệu để lưu vào bảng group_teams
        $groupData = [];
        foreach ($groups as $groupName => $teamIds) {
            $groupData[] = [
                'season_id' => $seasonId,
                'group' => $groupName,
                'team_ids' => json_encode($teamIds),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Lưu dữ liệu vào bảng group_teams
        DB::table('group_teams')->insert($groupData);

        // Gọi các hàm khởi tạo standings và matches
        $this->makeGroupStageStandings($seasonId, $groupData);
        $this->makeGroupStageMatches($seasonId, $groupData);
    }

    private function makeGroupStageStandings($seasonId, $groups)
    {
        $standings = [];

        foreach ($groups as $group) {
            $teamIds = json_decode($group['team_ids'], true); // Đọc danh sách đội từ JSON
            foreach ($teamIds as $teamId) {
                $standings[] = [
                    'season_id' => $seasonId,
                    'team_id' => $teamId,
                    'group' => $group['group'], // Sửa truy cập group
                    'match_played' => 0,
                    'goal_scored' => 0,
                    'goal_conceded' => 0,
                    'goal_difference' => 0,
                    'points' => 0,
                    'position' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        // Lưu vào bảng group_stage_standings
        DB::table('group_stage_standings')->insert($standings);
    }

    private function makeGroupStageMatches($seasonId, $groups)
    {
        $matches = [];

        foreach ($groups as $group) {
            $teamIds = json_decode($group['team_ids'], true);
            $teamIds = array_reverse($teamIds);
            $numTeams = count($teamIds);

            // Thêm "bye" nếu số đội lẻ
            if ($numTeams % 2 !== 0) {
                $teamIds[] = null;
                $numTeams++;
            }

            $rounds = $numTeams - 1;

            for ($round = 0; $round < $rounds; $round++) {
                for ($i = 0; $i < $numTeams / 2; $i++) {
                    $team1 = $teamIds[$i];
                    $team2 = $teamIds[$numTeams - 1 - $i];

                    if ($team1 !== null && $team2 !== null) {
                        $matches[] = [
                            'season_id' => $seasonId,
                            'group' => $group['group'],
                            'round' => $round + 1,
                            'team1_id' => $team1,
                            'team2_id' => $team2,
                            'team1_score' => null,
                            'team2_score' => null,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                }

                // Xoay các đội để tạo vòng tiếp theo
                $rotated = [$teamIds[0]]; // Đội đầu tiên giữ nguyên
                $remainingTeams = array_slice($teamIds, 1);
                array_unshift($remainingTeams, array_pop($remainingTeams));
                $teamIds = array_merge($rotated, $remainingTeams);
            }
        }
        $matches = collect($matches)->sortBy('round')->values()->toArray();
        DB::table('group_stage_matches')->insert($matches);
    }

    public function createEliminateStage($seasonId)
    {
        // Kiểm tra nếu group_stage_standings của season hiện tại có đủ 32 match thì thôi không cần thêm match mới vào nữa
        $existingMatchesCount = DB::table('eliminate_stage_matches')
            ->where('season_id', $seasonId)
            ->count();
        if ($existingMatchesCount >= 32) {
            return redirect()->route('cup.seasons.index')->with('fail', 'Season already have eliminate.');
        }

        $topTeams = DB::table('group_stage_standings')
            ->join('teams', 'group_stage_standings.team_id', '=', 'teams.id')
            ->whereIn('group_stage_standings.position', [1, 2, 3, 4]) // Chỉ lấy từ vị trí 1 đến 4
            ->where('group_stage_standings.season_id', $seasonId)
            ->orderBy('group_stage_standings.group', 'asc')
            ->orderBy('group_stage_standings.position', 'asc')
            ->get()
            ->groupBy('group'); // Nhóm theo `group`

        // Kiểm tra nếu không đủ dữ liệu trong các bảng
        foreach (['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'] as $group) {
            if (!isset($topTeams[$group]) || $topTeams[$group]->count() < 4) {
                return redirect()->route('cup.seasons.index')
                    ->with('fail', "Group $group does not have enough teams in positions 1 to 4.");
            }
        }
        // dd($topTeams);

        $branches = [
            [['A1', 'B4'], ['D2', 'G3'], ['E1', 'F4'], ['H2', 'C3']],
            [['A4', 'H1'], ['C2', 'F3'], ['D1', 'E4'], ['G2', 'B3']],
            [['C1', 'D4'], ['A3', 'F2'], ['G1', 'H4'], ['B2', 'E3']],
            [['F1', 'G4'], ['A2', 'D3'], ['B1', 'C4'], ['E2', 'H3']]
        ];

        $matches = [];
        foreach ($branches as $branchIndex => $branch) {
            foreach ($branch as $match) {
                [$team1Key, $team2Key] = $match;

                $team1Group = substr($team1Key, 0, 1);
                $team2Group = substr($team2Key, 0, 1);

                $team1Position = (int) substr($team1Key, 1, 1) - 1;
                $team2Position = (int) substr($team2Key, 1, 1) - 1;

                $team1 = $topTeams[$team1Group]->values()->get($team1Position);
                $team2 = $topTeams[$team2Group]->values()->get($team2Position);

                $matches[] = [
                    'season_id' => $seasonId,
                    'round' => 'round_of_32',
                    'branch' => $branchIndex + 1,
                    'team1_id' => $team1->id,
                    'team2_id' => $team2->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }
        $rounds = [
            'round_of_16' => 8,
            'quarter_finals' => 4,
            'semi_finals' => 2,
            'third_place' => 1,
            'final' => 1
        ];

        foreach ($rounds as $round => $numMatches) {
            for ($i = 1; $i <= $numMatches; $i++) {
                $matches[] = [
                    'season_id' => $seasonId,
                    'round' => $round,
                    'branch' => $i,
                    'team1_id' => null,
                    'team2_id' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }
        // dd($matches);
        DB::table('eliminate_stage_matches')->insert($matches);
        return redirect()->route('cup.seasons.index')->with('success', 'Season deleted successfully.');
    }
}
