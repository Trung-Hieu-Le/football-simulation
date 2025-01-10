<?php

namespace App\Http\Controllers\Tier;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class SeasonTierController extends Controller
{
    // Danh sách seasons
    public function index()
    {
        // Lấy danh sách các mùa giải
        $seasons = DB::table('seasons')->orderBy('season', 'desc')->get();

        // Thêm thông tin tỷ lệ trận và vòng vào từng season
        $seasons = $seasons->map(function ($season) {
            // Tổng số trận của mùa giải
            $totalMatches = DB::table('matches')
                ->where('season_id', $season->id)
                ->count();

            // Số trận đã có tỉ số
            $completedMatchesCount = DB::table('matches')
                ->where('season_id', $season->id)
                ->whereNotNull('team1_score')
                ->whereNotNull('team2_score')
                ->count();

            // Tính tỷ lệ số trận đã có tỉ số
            $matchCompletionRate = $totalMatches > 0 ? ($completedMatchesCount / $totalMatches) * 100 : 0;

            // Xác định round hiện tại và round tối đa
            $currentRound = DB::table('matches')
                ->where('season_id', $season->id)
                ->where(function ($query) {
                    $query->whereNull('team1_score')
                        ->orWhereNull('team2_score');
                })
                ->orderBy('round', 'asc')
                ->value('round');

            $maxRound = floor($season->teams_count / 3) - 1;
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

        return view('tier.seasons.index', compact('seasons'));
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
        $lastSeason = DB::table('seasons')->orderBy('id', 'desc')->first();

        $nextSeason = $lastSeason ? $lastSeason->season + 1 : 1;
        $nextTeamsCount = $lastSeason ? $lastSeason->teams_count : 64;

        return view('tier.seasons.create', compact('nextSeason', 'nextTeamsCount'));
    }



    // Hiển thị chi tiết season
    public function show($id)
    {
        $season = DB::table('seasons')->where('id', $id)->first();

        // Lấy thông tin bảng xếp hạng từ histories
        $groupStandings = DB::table('histories')
            ->select('histories.*', 'teams.name as team_name', 'teams.color_1', 'teams.color_2', 'teams.color_3')
            ->join('teams', 'teams.id', 'histories.team_id')
            ->where('season_id', $id)
            ->orderBy('tier', 'asc')
            ->orderBy('position', 'asc')
            ->get()
            ->groupBy('tier');

        //TODO: currentRound không vượt quá giá trị quy định
        $currentRound = DB::table('matches')
            ->where('season_id', $id)
            ->where(function ($query) {
                $query->whereNull('team1_score')
                    ->orWhereNull('team2_score');
            })
            ->orderBy('round', 'asc')
            ->value('round');

        $maxRound = floor($season->teams_count / 3) - 1;
        $currentRound = $currentRound ?? $maxRound + 1;
        $promotionRelegationCount = floor($season->teams_count / 12);

        // Lấy thông tin các trận đấu đã xảy ra và sắp tới
        $completedMatches = DB::table('matches')
            ->join('teams as t1', 'matches.team1_id', '=', 't1.id')
            ->join('teams as t2', 'matches.team2_id', '=', 't2.id')
            ->where('matches.team1_score', '!=', null)
            ->where('matches.team2_score', '!=', null)
            ->where('matches.round', '=', $currentRound - 1) // Chỉ lấy round trước
            ->where('matches.season_id', $id)
            ->select(
                'matches.*',
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
        $nextMatches = DB::table('matches')
            ->join('teams as t1', 'matches.team1_id', '=', 't1.id')
            ->join('teams as t2', 'matches.team2_id', '=', 't2.id')
            ->where('matches.round', '=', $currentRound) // Chỉ lấy round hiện tại
            ->where('matches.season_id', $id)
            ->select(
                'matches.*',
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

        $champion = null;
        $promotedTeams = collect();
        $relegatedTeams = collect();

        if ($currentRound === $maxRound + 1) {
            $champion = $groupStandings['tier1']->where('position', 1)->first();

            foreach ($groupStandings as $tierKey => $standings) {
                $tierNumber = (int) str_replace('tier', '', $tierKey);
                if ($tierNumber > 1) {
                    $promotedTeams = $promotedTeams->merge($standings->where('position', '<=', $promotionRelegationCount));
                }
                if ($tierNumber < count($groupStandings)) {
                    $relegatedTeams = $relegatedTeams->merge($standings->where('position', '>', $standings->count() - $promotionRelegationCount));
                }
            }
            foreach ($groupStandings as $tierKey => $standings) {
                foreach ($standings as $team) {
                    $result = 'stay'; // Mặc định là stay

                    if ($champion && $team->team_id == $champion->team_id) {
                        $result = 'champion';
                    } elseif ($promotedTeams->contains('team_id', $team->team_id)) {
                        $result = 'promoted';
                    } elseif ($relegatedTeams->contains('team_id', $team->team_id)) {
                        $result = 'relegated';
                    }

                    DB::table('histories')
                        ->where('id', $team->id)
                        ->update(['result' => $result]);
                }
            }
        }

        return view('tier.seasons.show', compact('season', 'groupStandings', 'completedMatches', 'nextMatches', 'currentRound', 'maxRound', 'champion', 'promotedTeams', 'relegatedTeams'));
    }

    public function listMatches(Request $request)
    {
        $seasonId = $request->id;
        $matchesByRound = DB::table('matches')->orderBy('round')
            ->join('teams as t1', 'matches.team1_id', '=', 't1.id')
            ->join('teams as t2', 'matches.team2_id', '=', 't2.id')
            ->select(
                'matches.*',
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
        return view('tier.seasons.matches', compact('matchesByRound', 'seasonId'));
    }
    public function showStatistics(Request $request)
    {
        $sortBy = $request->get('sort_by', 'points');
        $seasonId = $request->id;
        $histories = DB::table('histories')->selectRaw("
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
            ->join('teams', 'histories.team_id', '=', 'teams.id')
            ->where('season_id', $seasonId)
            ->groupBy('team_id')
            ->orderBy($sortBy, 'desc')
            ->get();

        return view('tier.seasons.histories', compact('histories', 'sortBy', 'seasonId'));
    }





    // Lưu season và phân chia teams
    public function store(Request $request)
    {
        if ($request->teams_count % 12 !== 0) {
            return redirect()->back()->withErrors(['teams_count' => 'The number of teams must be divisible by 12.']);
        }

        $metaOptions = ['attack', 'defense', 'control', 'aggressive', 'stamina', 'penalty'];
        $meta = $request->meta ?: $metaOptions[array_rand($metaOptions)]; // Nếu không chọn thì lấy random

        $seasonId = DB::table('seasons')->insertGetId([
            'season' => $request->season,
            'teams_count' => $request->teams_count,
            'meta' => $meta,
        ]);

        $this->assignTeamsToTiers($seasonId, $request->teams_count);
        $this->createHistories($seasonId, $request->teams_count);
        $this->createMatches($seasonId);

        return redirect()->route('seasons.index')->with('success', 'Season created successfully.');
    }

    // Phân chia teams thành tiers
    private function assignTeamsToTiers($seasonId, $teamsCount)
    {
        // Lấy danh sách các đội của mùa mới
        $teams = DB::table('teams')->orderBy('id')->take($teamsCount)->get();

        // Tìm Last Season
        $lastSeason = DB::table('histories')
            ->where('season_id', '<', $seasonId)
            ->orderBy('season_id', 'desc')
            ->first();

        // Nếu không có last season, phân chia đều các đội và return
        if (!$lastSeason) {
            $teamsPerTier = ceil($teamsCount / 3);

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
            return;
        }

        // Lấy danh sách team histories của last season
        $lastSeasonHistories = DB::table('histories')
            ->where('season_id', $lastSeason->season_id)
            ->orderBy('tier')
            ->orderBy('position')
            ->get();

        // Lọc các đội promoted và relegated
        $promotedTeams = $lastSeasonHistories->where('result', 'promoted');
        $relegatedTeams = $lastSeasonHistories->where('result', 'relegated');
        // dd($promotedTeams, $relegatedTeams);
        // Tạo mảng cho 3 tier
        $tier1 = [];
        $tier2 = [];
        $tier3 = [];

        // Xử lý các đội promoted và relegated
        foreach ($promotedTeams as $team) {
            if ($team->tier === 'tier3' && $teams->contains('id', $team->team_id)) {
                $tier2[] = $team->team_id;
            } elseif ($team->tier === 'tier2' && $teams->contains('id', $team->team_id)) {
                $tier1[] = $team->team_id;
            }
        }

        foreach ($relegatedTeams as $team) {
            if ($team->tier === 'tier1' && $teams->contains('id', $team->team_id)) {
                $tier2[] = $team->team_id;
            } elseif ($team->tier === 'tier2' && $teams->contains('id', $team->team_id)) {
                $tier3[] = $team->team_id;
            }
        }

        // Lấy danh sách các đội còn lại từ last season, không tính các đội promoted và relegated
        $remainingTeams = $lastSeasonHistories->whereNotIn('team_id', $promotedTeams->pluck('team_id')->merge($relegatedTeams->pluck('team_id')));

        // Thêm các đội còn lại vào các tier theo thứ tự
        foreach ($remainingTeams as $team) {
            if (count($tier1) < $teamsCount / 3) {
                $tier1[] = $team->team_id;
            } elseif (count($tier2) < $teamsCount / 3) {
                $tier2[] = $team->team_id;
            } else {
                $tier3[] = $team->team_id;
            }
        }

        // Thêm các đội mới vào các tier nếu còn thiếu
        foreach ($teams as $team) {
            if (!in_array($team->id, $tier1) && !in_array($team->id, $tier2) && !in_array($team->id, $tier3)) {
                if (count($tier1) < $teamsCount / 3) {
                    $tier1[] = $team->id;
                } elseif (count($tier2) < $teamsCount / 3) {
                    $tier2[] = $team->id;
                } else {
                    $tier3[] = $team->id;
                }
            }
        }

        // Cập nhật vào team_groups
        $tiers = [
            'tier1' => $tier1,
            'tier2' => $tier2,
            'tier3' => $tier3,
        ];

        foreach ($tiers as $tierName => $tierTeams) {
            DB::table('team_groups')->updateOrInsert(
                [
                    'season_id' => $seasonId,
                    'tier' => $tierName,
                ],
                [
                    'team_ids' => implode(',', $tierTeams),
                    'created_at' => now(),
                ]
            );
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
        $teamGroups = DB::table('team_groups')->where('season_id', $seasonId)->orderBy('tier', 'desc')->get();

        $matches = [];

        foreach ($teamGroups as $group) {
            $teamIds = explode(',', $group->team_ids);

            $teamIds = array_reverse($teamIds);
            $groupMatches = $this->generateGroupMatches($seasonId, $teamIds, $group->tier);
            $matches = array_merge($matches, $groupMatches);
        }

        usort($matches, function ($a, $b) {
            return $a['round'] <=> $b['round'];
        });

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
