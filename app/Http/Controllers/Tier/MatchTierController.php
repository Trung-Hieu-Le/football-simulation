<?php

namespace App\Http\Controllers\Tier;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class MatchTierController extends Controller
{
    public function simulateMatch(Request $request)
    {
        $season_id = $request->input('season_id');
        $match_count = $request->input('match_count', 1); // Giá trị mặc định là 1 nếu không nhập
        $season_meta = DB::table('seasons')->where('id', $season_id)->value('meta');
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
            $team1_shots = 0;
            $team1_shots_on_target = 0;
            $team2_shots = 0;
            $team2_shots_on_target = 0;

            // Simulate each half
            for ($half = 1; $half <= 2; $half++) {
                $currentTeam = rand(1, 2); // Team bắt đầu hiệp
                $time = ($half == 1) ? 0 : 45; // Thời gian bắt đầu hiệp

                $staminaFactor1 = ($half == 2) ? 0.5 + ($team1->stamina / 200) : 1;
                $staminaFactor2 = ($half == 2) ? 0.5 + ($team2->stamina / 200) : 1;
                $formFactor1 = 1 + ($team1->form / 1000);
                $formFactor2 = 1 + ($team2->form / 1000);

                // Nhân các chỉ số với formFactor
                // Hệ số theo meta
                $metaFactor = 1.1;
                $nonMetaFactor = 0.9;
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

                // Điều chỉnh chỉ số theo meta
                if ($season_meta == 'attack') {
                    $team1_attack = $team1_attack * $metaFactor;
                    $team2_attack = $team2_attack * $metaFactor;
                } elseif ($season_meta == 'defense') {
                    $team1_defense = $team1_defense * $metaFactor;
                    $team2_defense = $team2_defense * $metaFactor;
                } elseif ($season_meta == 'control') {
                    $team1_control = $team1_control * $metaFactor;
                    $team2_control = $team2_control * $metaFactor;
                    $team1_attack = $team1_attack * $nonMetaFactor;
                    $team2_attack = $team2_attack * $nonMetaFactor;
                } elseif ($season_meta == 'aggressive') {
                    $team1_aggressive = $team1_aggressive * $metaFactor;
                    $team2_aggressive = $team2_aggressive * $metaFactor;
                    $team1_defense = $team1_defense * $nonMetaFactor;
                    $team2_defense = $team2_defense * $nonMetaFactor;
                } elseif ($season_meta == 'penalty') {
                    $team1_penalty = $team1_penalty * 2;
                    $team2_penalty = $team2_penalty * 2;
                    $team1_attack = $team1_attack * $nonMetaFactor;
                    $team2_attack = $team2_attack * $nonMetaFactor;
                    $team1_defense = $team1_defense * $metaFactor;
                    $team2_defense = $team2_defense * $metaFactor;
                    $team1_aggressive = $team1_aggressive * $nonMetaFactor;
                    $team2_aggressive = $team2_aggressive * $nonMetaFactor;
                } elseif ($season_meta == 'stamina') {
                    $staminaFactor1 = $staminaFactor1 * $metaFactor;
                    $staminaFactor2 = $staminaFactor2 * $metaFactor;
                    $team1_control = $team1_control * $metaFactor;
                    $team2_control = $team2_control * $metaFactor;
                    $team1_attack = $team1_attack * $nonMetaFactor;
                    $team2_attack = $team2_attack * $nonMetaFactor;
                    $team1_defense = $team1_defense * $nonMetaFactor;
                    $team2_defense = $team2_defense * $nonMetaFactor;
                }

                // Nếu là hiệp 2, nhân thêm với staminaFactor
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
                $team1_total = $team1_attack + $team1_defense + $team1_control + $team1_aggressive + $team1_penalty + $team1->stamina * $staminaFactor1;
                $team2_total = $team2_attack + $team2_defense + $team2_control + $team2_aggressive + $team2_penalty + $team2->stamina * $staminaFactor2;

                for ($i = 0; $i < 45; $i++) { // Mỗi hiệp 45 lần xử lý
                    $time += 1;
                    
                    if ($currentTeam == 1) {
                        $team1_possession++;
                    } else {
                        $team2_possession++;
                    }

                    $state = $state ?? rand(50, 80);

                    if ($state <= 50) { // "Ưu thế"
                        $team1_control_chance = rand(0, 40) * $team1_control;
                        $team2_control_chance = rand(0, 60) * $team2_control;
                        if ($team1_score - $team2_score >= 2) {
                            $team2_control_chance = rand(0, 80) * $team2_control;
                        } elseif ($team2_score - $team1_score >= 2) {
                            $team1_control_chance = rand(0, 100) * $team1_control;
                        }
                        if (($currentTeam == 1 && $team1_control_chance > $team2_control_chance) || ($currentTeam == 2 && $team2_control_chance > $team1_control_chance)) {
                            $state = 70; // Sang "Cơ hội"
                        } else {
                            $currentTeam = 3 - $currentTeam; // Đội kia cướp bóng
                            $state = rand(50, 100);
                        }
                    }

                    if ($state > 50 && $state <= 80) { // "Cơ hội"
                        $team1_attack_chance = rand(0, 40) * ($team1_attack + $team1_control);
                        $team1_defense_chance = rand(0, 40) * ($team1_defense + $team1_control);
                        $team2_attack_chance = rand(0, 40) * ($team2_attack + $team2_control);
                        $team2_defense_chance = rand(0, 40) * ($team2_defense + $team2_control);
                        if ($team1_score - $team2_score >= 2) {
                            $team2_attack_chance = rand(0, 60) * ($team2_attack + $team2_control);
                            $team2_defense_chance = rand(0, 60) * ($team2_defense + $team2_control);
                        } elseif ($team2_score - $team1_score >= 2) {
                            $team1_attack_chance = rand(0, 60) * ($team1_attack + $team1_control);
                            $team1_defense_chance = rand(0, 60) * ($team1_defense + $team1_control);
                        }
                        if (($currentTeam == 1 && $team1_attack_chance > $team2_defense_chance) || ($currentTeam == 2 && $team2_attack_chance > $team1_defense_chance)) {
                            $state = 90; // Sang "Nguy hiểm"
                        } else {
                            $currentTeam = 3 - $currentTeam; // Đội kia cướp bóng
                            $state = rand(80, 100);
                        }
                    }

                    if ($state > 80 && $state < 95) { // "Nguy hiểm"
                        $team1_attack_chance = rand(0, 40) * $team1_attack;
                        $team1_defense_chance = rand(0, 50) * $team1_defense;
                        $team2_attack_chance = rand(0, 40) * $team2_attack;
                        $team2_defense_chance = rand(0, 50) * $team2_defense;
                        if ($team1_score - $team2_score >= 2) {
                            $team2_attack_chance = rand(0, 60) * $team2_attack;
                            $team2_defense_chance = rand(0, 70) * $team2_defense;
                        } elseif ($team2_score - $team1_score >= 2) {
                            $team1_attack_chance = rand(0, 60) * $team1_attack;
                            $team1_defense_chance = rand(0, 70) * $team1_defense;
                        }
                        $long_shot_chance = rand(1, 1000);
                        $long_shot_threshold_team1 = $team1_total < $team2_total ? 10 : 1; // 5% cho đội yếu hơn, 1% cho đội mạnh hơn
                        $long_shot_threshold_team2 = $team2_total < $team1_total ? 10 : 1;

                        if ($long_shot_chance <= $long_shot_threshold_team1 && $currentTeam == 1) {
                            $team1_score++;
                            $team1_shots++;
                            $dangerousSituations[] = "$time': Long Shot Goal by $team1->name!";
                            $currentTeam = 3 - $currentTeam; // Đội kia cướp bóng 
                            $state = null; // Reset state
                        } elseif ($long_shot_chance <= $long_shot_threshold_team2 && $currentTeam == 2) {
                            $team2_score++;
                            $team2_shots++;
                            $dangerousSituations[] = "$time': Long Shot Goal by $team2->name!";
                            $currentTeam = 3 - $currentTeam; // Đội kia cướp bóng 
                            $state = null; // Reset state
                        }
                        if (($currentTeam == 1 && $team1_attack_chance > $team2_defense_chance) || ($currentTeam == 2 && $team2_attack_chance > $team1_defense_chance)) {
                            $aggressive_chance = rand(0, 100) * ($currentTeam == 1 ? $team2_aggressive : $team1_aggressive);
                            if (($team1_score - $team2_score >= 2 && $currentTeam == 2) || ($team2_score - $team1_score >= 2 && $currentTeam == 1)) {
                                $aggressive_chance *= 0.5; // Giảm thêm 30%
                            }
                            if ($aggressive_chance >= rand(0, 100) * 100) {
                                $currentTeam = 3 - $currentTeam; // Đội kia cướp bóng 
                                $state = rand(80, 100);
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
                            $state = rand(80, 100);
                        }
                    }

                    if ($state >= 95) { // "Thành bàn"
                        $team1_attack_chance = rand(0, 30) * $team1_attack;
                        $team1_defense_chance = rand(0, 80) * $team1_defense;
                        $team2_attack_chance = rand(0, 30) * $team2_attack;
                        $team2_defense_chance = rand(0, 80) * $team2_defense;
                        $aggressive_chance = rand(0, 100) * ($currentTeam == 1 ? $team2_aggressive : $team1_aggressive);
                        if ($team1_score - $team2_score >= 1) {
                            // $team2_attack_chance = rand(40, 100) * $team2_attack;
                            $team2_defense_chance = rand(0, 100) * $team2_defense;
                            $aggressive_chance *= 0.5; // Giảm aggressive
                        } elseif ($team2_score - $team1_score >= 1) {
                            // $team1_attack_chance = rand(40, 100) * $team1_attack;
                            $team1_defense_chance = rand(0, 100) * $team1_defense;
                            $aggressive_chance *= 0.5; // Giảm aggressive
                        }
                        if (($currentTeam == 1 && $team1_attack_chance > $team2_defense_chance) || ($currentTeam == 2 && $team2_attack_chance > $team1_defense_chance)) {
                            if ($currentTeam == 1) {
                                $team1_shots++;
                                if (rand(1, 100) <= 50) {
                                    $team1_shots_on_target++;
                                    if (rand(1, 100) <= 50) {
                                        $team1_score++;
                                        $dangerousSituations[] = "$time': Near Shot Goal by $team1->name!";
                                        $currentTeam = 3 - $currentTeam;
                                        $state = null;
                                    } else {
                                        $currentTeam = 3 - $currentTeam; // Đội kia cướp bóng 
                                        $state = rand(50, 80);
                                    }
                                }
                            } else {
                                $team2_shots++;
                                if (rand(1, 100) <= 50) {
                                    $team2_shots_on_target++;
                                    if (rand(1, 100) <= 50) {
                                        $team2_score++;
                                        $dangerousSituations[] = "$time': Near Shot Goal by $team2->name!";
                                        $currentTeam = 3 - $currentTeam;
                                        $state = null;
                                    } else {
                                        $currentTeam = 3 - $currentTeam; // Đội kia cướp bóng 
                                        $state = rand(50, 80);
                                    }
                                }
                            }
                        } else {
                            if ($aggressive_chance >= rand(30, 100) * 100) {
                                $currentTeam = 3 - $currentTeam; // Đội kia cướp bóng 
                                $state = rand(80, 100);
                            } else {
                                if (rand(1, 100) <= 30) {
                                    $team1_penalty_chance = rand(10, 30) * $team1_penalty;
                                    $team2_penalty_chance = rand(10, 30) * $team2_penalty;
                                    if ($currentTeam == 1) {
                                        $team1_shots++;
                                        if (rand(1, 100) <= 20) {
                                            $team2_fouls++;
                                            $dangerousSituations[] = "$time': Foul by $team2->name (P)";
                                            if (rand(1, 100) <= 90) { // Giả sử 80% sút trúng mục tiêu
                                                $team1_shots_on_target++;
                                                if ($team1_penalty_chance > $team2_penalty_chance) {
                                                    $team1_score++;
                                                    $dangerousSituations[] = "$time': Penalty Goal by $team1->name (P)!";
                                                }
                                            }
                                        }
                                    } else {
                                        $team2_shots++;
                                        if (rand(1, 100) <= 20) {
                                            $team1_fouls++;
                                            $dangerousSituations[] = "$time': Foul by $team1->name (P)";
                                            if (rand(1, 100) <= 90) { // Giả sử 80% sút trúng mục tiêu
                                                $team2_shots_on_target++;
                                                if ($team2_penalty_chance > $team1_penalty_chance) {
                                                    $team2_score++;
                                                    $dangerousSituations[] = "$time': Penalty Goal by $team2->name (P)!";
                                                }
                                            }
                                        }
                                    }
                                    $currentTeam = 3 - $currentTeam; // Đội kia cướp bóng 
                                    $state = null;
                                } else {
                                    $currentTeam = 3 - $currentTeam; // Đội kia cướp bóng 
                                    $state = rand(50, 80);
                                }
                            }
                        }
                    }
                }
            }


            $team1_possession = round($team1_possession, 2);
            $team2_possession = 100 - $team1_possession;

            // dd(
            //     "team1id: " . $match->team1_id,
            //     "Team1score: " . $team1_score,
            //     "Team1foul: " . $team1_fouls,
            //     "Team1control: " . $team1_possession,
            //     "team2id: " . $match->team2_id,
            //     "Team2score: " . $team2_score,
            //     "Team2foul: " . $team2_fouls,
            //     "Team2control: " . $team2_possession,
            //     "Total index: " . $team1_total . " - " . $team2_total,
            //     "Shots: " . $team1_shots . " - " . $team2_shots,
            //     "Shots on target: " . $team1_shots_on_target . " - " . $team2_shots_on_target,
            //     $dangerousSituations,
            //     $team1,
            //     $team2
            // );

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
                'team1_shots' => $team1_shots,
                'team2_shots' => $team2_shots,
                'team1_shots_on_target' => $team1_shots_on_target,
                'team2_shots_on_target' => $team2_shots_on_target,
                'team1_possession' => $team1_possession,
                'team2_possession' => $team2_possession,
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
        $form = max(5, min(100, $form));
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
                    $team->win,
                    $team->goal_scored,
                    -$team->foul,
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
