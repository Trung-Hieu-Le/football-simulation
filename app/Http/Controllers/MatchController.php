<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MatchController extends Controller
{
    public function simulateMatch(Request $request)
    {
        $season_id = $request->input('season_id');
        $match_count = $request->input('match_count', 1); // Giá trị mặc định là 1 nếu không nhập

        $nextMatches = DB::table('matches')
            ->where('season_id', $season_id)
            ->whereNull('team1_score')
            ->whereNull('team2_score')
            ->limit($match_count)
            ->get();
        $dangerousSituations = [];
        foreach ($nextMatches as $match) {
            $team1 = DB::table('teams')->where('id', $match->team1_id)->first();
            $team2 = DB::table('teams')->where('id', $match->team2_id)->first();

            $team1_score = 0;
            $team2_score = 0;
            $team1_fouls = 0;
            $team2_fouls = 0;
            $team1_possession = 0;
            $team2_possession = 0;

            // Simulate each half
            for ($half = 1; $half <= 2; $half++) {
                $currentTeam = rand(1, 2); // Team bắt đầu hiệp
                $time = ($half == 1) ? 0 : 45; // Thời gian bắt đầu hiệp

                $staminaFactor1 = ($half == 2) ? $team1->stamina / 100 : 1;
                $staminaFactor2 = ($half == 2) ? $team2->stamina / 100 : 1;
                $formFactor1 = 1 + ($team1->form / 200);
                $formFactor2 = 1 + ($team2->form / 200);
                $team1_attack = $team1->attack * $formFactor1;
                $team2_attack = $team2->attack * $formFactor2;
                $team1_defense = $team1->defense * $formFactor1;
                $team2_defense = $team2->defense * $formFactor2;
                $team1_control = $team1->control * $formFactor1;
                $team2_control = $team2->control * $formFactor2;
                $team1_aggressive = $team1->aggressive * $formFactor1;
                $team2_aggressive = $team2->aggressive * $formFactor2;
                $team1_penalty = $team1->penalty * $formFactor1;
                $team2_penalty = $team2->penalty * $formFactor2;
                if ($half == 2) {
                    $team1_attack *= $staminaFactor1;
                    $team2_attack *= $staminaFactor2;
                    $team1_defense *= $staminaFactor1;
                    $team2_defense *= $staminaFactor2;
                    $team1_control *= $staminaFactor1;
                    $team2_control *= $staminaFactor2;
                    $team1_aggressive *= $staminaFactor1;
                    $team2_aggressive *= $staminaFactor2;
                    $team1_penalty *= $staminaFactor1;
                    $team2_penalty *= $staminaFactor2;
                }
                for ($i = 0; $i < 45; $i++) { // Mỗi hiệp 45 lần xử lý
                    $time += 1;
                    if ($currentTeam == 1) {
                        $team1_possession++;
                    } else {
                        $team2_possession++;
                    }

                    $state = rand(0, 80);

                    if ($state <= 50) { // "Ưu thế"
                        if (rand(1, 100) <= 60 * ($currentTeam == 1 ? $team1->control : $team2->control) / ($currentTeam == 2 ? $team1->control : $team2->control)) {
                            $state = 70; // Sang "Cơ hội"
                        } else {
                            $currentTeam = 3 - $currentTeam; // Đội kia cướp bóng
                        }
                    }

                    if ($state > 50 && $state <= 80) { // "Cơ hội"
                        if (rand(1, 100) <= 50 * ($currentTeam == 1 ? $team1->attack + $team1->control : $team2->attack + $team2->control) / ($currentTeam == 2 ? $team1->defense + $team1->control : $team2->defense + $team2->control)) {
                            $state = 90; // Sang "Nguy hiểm"
                        } else {
                            $currentTeam = 3 - $currentTeam; // Đội kia cướp bóng
                        }
                    }

                    if ($state > 80 && $state < 100) { // "Nguy hiểm"
                        if (rand(1, 100) <= 40 * ($currentTeam == 1 ? $team1->attack : $team2->attack) / ($currentTeam == 2 ? $team1->defense : $team2->defense)) {
                            if (rand(1, 100) <= 100 * ($currentTeam == 1 ? $team2->aggressive : $team1->aggressive) / 100) {
                                $currentTeam = 3 - $currentTeam; // Đội kia cướp bóng 
                            } else {
                                if ($currentTeam == 1) {
                                    if (rand(1, 100) <= 30) {
                                        $team2_fouls++;
                                        $dangerousSituations[] = "$time': Foul by $team2->name";
                                    }
                                    $currentTeam = 1; // Giữ bóng
                                } else {
                                    if (rand(1, 100) <= 30) {
                                        $team1_fouls++;
                                        $dangerousSituations[] = "$time': Foul by $team1->name";
                                    }
                                    $currentTeam = 2; // Giữ bóng
                                }
                                $state = 100; // Sang "Thành bàn"
                            }
                        } else {
                            $currentTeam = 3 - $currentTeam; // Đội kia cướp bóng 
                        }
                    }

                    if ($state == 100) { // "Thành bàn"
                        if (rand(1, 100) <= 20 * ($currentTeam == 1 ? $team1->attack : $team2->attack) / ($currentTeam == 2 ? $team1->defense : $team2->defense)) { // Tăng tỉ lệ goal ghi bàn
                            // dd($currentTeam, 10 * ($currentTeam == 1 ? $team1->attack : $team2->attack) / ($currentTeam == 2 ? $team1->defense : $team2->defense));
                            if ($currentTeam == 1) {
                                $team1_score++;
                                $dangerousSituations[] = "$time': Goal by $team1->name";
                            } else {
                                $team2_score++;
                                $dangerousSituations[] = "$time': Goal by $team2->name";
                            }
                            $currentTeam = 3 - $currentTeam; // Đội kia bắt đầu lại từ "Ưu thế"
                        } else {
                            if (rand(1, 100) <= 70 * ($currentTeam == 1 ? $team2->aggressive : $team1->aggressive) / 100) {
                                if ($currentTeam == 1) {
                                    if (rand(1, 100) <= 30) {
                                        $team2_fouls++;
                                        $dangerousSituations[] = "$time': Foul by $team2->name (Penalty)";
                                    }
                                    if (rand(1, 100) <= 20 * $team1->penalty / $team2->penalty) {
                                        $team1_score++;
                                        $dangerousSituations[] = "$time': Penalty Goal by $team1->name (P)";
                                    }
                                } else {
                                    if (rand(1, 100) <= 30) {
                                        $team1_fouls++;
                                        $dangerousSituations[] = "$time': Foul by $team1->name (Penalty)";
                                    }
                                    if (rand(1, 100) <= 20 * $team2->penalty / $team1->penalty) {
                                        $team2_score++;
                                        $dangerousSituations[] = "$time': Penalty Goal by $team2->name (P)";
                                    }
                                }
                            } else {
                                $currentTeam = 3 - $currentTeam; // Đội kia cướp bóng
                            }
                        }
                    }
                }
            }

            $team1_possession = round($team1_possession, 2);
            $team2_possession = 100 - $team1_possession;

            dd(
                "team1id: " . $match->team1_id,
                "Team1score: " . $team1_score,
                "Team1foul: " . $team1_fouls,
                "Team1control: " . $team1_possession,
                "team2id: " . $match->team2_id,
                "Team2score: " . $team2_score,
                "Team2foul: " . $team2_fouls,
                "Team2control: " . $team2_possession,
                $dangerousSituations,
                $team1,
                $team2
            );

            DB::table('matches')
                ->where('id', $match->id)
                ->update([
                    'team1_score' => $team1_score,
                    'team2_score' => $team2_score,
                    'updated_at' => now(),
                    'team1_possession' => $team1_possession,
                    'team2_possession' => $team2_possession,
                    'team1_foul' => $team1_fouls,
                    'team2_foul' => $team2_fouls,
                    'updated_at' => now()
                ]);

            $this->updateHistory($match->team1_id, $match->season_id, $team1_score, $team2_score, $team1_fouls, $team1_possession);
            $this->updateHistory($match->team2_id, $match->season_id, $team2_score, $team1_score, $team2_fouls, $team2_possession);
            $this->updateStandings($match->season_id);
        }

        $matchResult = [];
        if (isset($team1) && isset($team2)) {
            $matchResult = [
                'team1_name' => $team1->name,
                'team2_name' => $team2->name,
                'team1_score' => $team1_score,
                'team2_score' => $team2_score,
                'dangerousSituations' => $dangerousSituations,
            ];
        }
        return redirect()->back()->with('success', 'Next matches simulated successfully!')
            ->with('matchResult', $matchResult);
    }

    private function updateHistory($teamId, $seasonId, $goalsScored, $goalsConceded, $fouls, $possession)
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
        $points = $history->points;
        $totalFouls = $history->foul + $fouls;
        $averagePossession = ($history->average_possession * $history->match_played + $possession) / $matchPlayed;
        $win = $history->win;
        $draw = $history->draw;
        $lose = $history->lose;

        if ($goalsScored > $goalsConceded) {
            $points += 3;
            $win += 1;
            DB::table('teams')->where('id', $teamId)->increment('form', 5);
        } elseif ($goalsScored == $goalsConceded) {
            $points += 1;
            $draw += 1;
        } else {
            $lose += 1;
            DB::table('teams')->where('id', $teamId)->decrement('form', 5);
        }

        $form = DB::table('teams')->where('id', $teamId)->value('form');
        $form = max(0, min(100, $form));
        DB::table('teams')->where('id', $teamId)->update(['form' => $form]);

        DB::table('histories')->updateOrInsert(
            ['team_id' => $teamId, 'season_id' => $seasonId],
            [
                'match_played' => $matchPlayed,
                'goal_scored' => $goalScored,
                'goal_conceded' => $goalConceded,
                'goal_difference' => $goalDifference,
                'points' => $points,
                'foul' => $totalFouls,
                'average_possession' => round($averagePossession, 2),
                'win' => $win,
                'draw' => $draw,
                'lose' => $lose,
                'updated_at' => now(),
            ]
        );
    }

    private function updateStandings($season_id)
    {
        $teamsHistory = DB::table('histories')
            ->join('teams', 'histories.team_id', '=', 'teams.id')
            ->where('histories.season_id', $season_id)
            ->select(
                'histories.*',
                DB::raw('COALESCE(teams.form, 0) as team_form')
            )
            ->get()
            ->groupBy('tier');

        foreach ($teamsHistory as $group => $groupTeams) {
            $sortedTeams = $groupTeams->sortByDesc(function ($team) {
                return [
                    $team->points,
                    $team->goal_difference,
                    $team->goal_scored,
                    -$team->foul,
                    $team->win,
                    $team->average_possession,
                    $team->team_form
                ];
            })->values();

            foreach ($sortedTeams as $index => $team) {
                DB::table('histories')
                    ->where('team_id', $team->team_id)
                    ->where('season_id', $season_id)
                    ->where('tier', $group)
                    ->update(['position' => $index + 1]);
            }
        }
    }
}
