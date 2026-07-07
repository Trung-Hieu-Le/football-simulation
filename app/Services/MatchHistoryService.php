<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class MatchHistoryService
{
    /**
     * Cập nhật lịch sử cho eliminate stage
     */
    public function updateEliminateHistory($teamId, $seasonId, $goalsScored, $goalsConceded, 
                                         $fouls, $possession, $title)
    {
<<<<<<< Updated upstream
        if (!$teamId) return;
        
        $history = DB::table('cup_standings')
            ->where('team_id', $teamId)
            ->where('season_id', $seasonId)
            ->first();
        
        if (!$history) return;
        
        $matchPlayed = $history->match_played + 1;
        $goalScored = $history->goal_scored + $goalsScored;
        $goalConceded = $history->goal_conceded + $goalsConceded;
        $goalDifference = $goalScored - $goalConceded;
        $totalFouls = $history->foul + $fouls;
        $averagePossession = ($history->average_possession * $history->match_played + $possession) / $matchPlayed;
        $win = $history->win;
        $draw = $history->draw;
        $lose = $history->lose;
        
        if ($goalsScored > $goalsConceded) {
            $win += 1;
            DB::table('teams')->where('id', $teamId)->increment('form', 5);
        } elseif ($goalsScored == $goalsConceded) {
            $draw += 1;
        } else {
            $lose += 1;
            DB::table('teams')->where('id', $teamId)->decrement('form', 5);
        }
        
        $form = DB::table('teams')->where('id', $teamId)->value('form');
        $form = max(5, min(100, $form));
        DB::table('teams')->where('id', $teamId)->update(['form' => $form]);
        
        $standing = DB::table('cup_standings')->updateOrInsert(
=======
        $this->eloService = $eloService;
    }

    public function updateLeagueMatchHistory(
        int $team1Id,
        int $team2Id,
        int $seasonId,
        string $division,
        int $team1Score,
        int $team2Score,
        int $team1Fouls,
        int $team2Fouls,
        int $team1Possession,
        int $team2Possession
    ): void {
        $team1 = Team::find($team1Id);
        $team2 = Team::find($team2Id);

        if (!$team1 || !$team2) {
            return;
        }

        $this->updateLeagueStanding($team1Id, $seasonId, $division, $team1Score, $team2Score, $team1Fouls, $team1Possession);
        $this->updateLeagueStanding($team2Id, $seasonId, $division, $team2Score, $team1Score, $team2Fouls, $team2Possession);

        $this->eloService->updateEloAfterMatch($team1, $team2, $team1Score, $team2Score);
    }

    public function updateCupGroupStageHistory(
        int $team1Id,
        int $team2Id,
        int $seasonId,
        int $team1Score,
        int $team2Score,
        int $team1Fouls,
        int $team2Fouls,
        int $team1Possession,
        int $team2Possession
    ): void {
        $team1 = Team::find($team1Id);
        $team2 = Team::find($team2Id);

        if (!$team1 || !$team2) {
            return;
        }

        $this->updateCupStanding($team1Id, $seasonId, $team1Score, $team2Score, $team1Fouls, $team1Possession);
        $this->updateCupStanding($team2Id, $seasonId, $team2Score, $team1Score, $team2Fouls, $team2Possession);

        $this->eloService->updateEloAfterMatch($team1, $team2, $team1Score, $team2Score);
    }

    public function updateCupEliminateHistory(
        int $team1Id,
        int $team2Id,
        int $seasonId,
        int $team1Score,
        int $team2Score,
        int $team1Fouls,
        int $team2Fouls,
        int $team1Possession,
        int $team2Possession,
        string $round,
        int $winnerId
    ): void {
        $team1 = Team::find($team1Id);
        $team2 = Team::find($team2Id);

        if (!$team1 || !$team2) {
            return;
        }

        $isTeam1Winner = $winnerId === $team1Id;

        $team1Result = CupSeasonResult::fromRound($round, $isTeam1Winner);
        $team2Result = CupSeasonResult::fromRound($round, !$isTeam1Winner);

        $this->updateCupStanding($team1Id, $seasonId, $team1Score, $team2Score, $team1Fouls, $team1Possession, $team1Result?->value);
        $this->updateCupStanding($team2Id, $seasonId, $team2Score, $team1Score, $team2Fouls, $team2Possession, $team2Result?->value);

        $this->eloService->updateEloAfterMatch($team1, $team2, $team1Score, $team2Score);
    }

    protected function updateLeagueStanding(
        int $teamId,
        int $seasonId,
        string $division,
        int $goalsScored,
        int $goalsConceded,
        int $fouls,
        int $possession
    ): void {
        $standing = LeagueStanding::firstOrCreate(
>>>>>>> Stashed changes
            ['team_id' => $teamId, 'season_id' => $seasonId],
            [
                'match_played' => $matchPlayed,
                'goal_scored' => $goalScored,
                'goal_conceded' => $goalConceded,
                'goal_difference' => $goalDifference,
                'foul' => $totalFouls,
                'average_possession' => round($averagePossession, 2),
                'win' => $win,
                'draw' => $draw,
                'lose' => $lose,
                'updated_at' => now(),
            ]
        );
        
        // Update position và result vào cup_positions
        $standingId = DB::table('cup_standings')
            ->where('team_id', $teamId)
            ->where('season_id', $seasonId)
            ->value('id');
        
        if ($standingId) {
            DB::table('cup_positions')->updateOrInsert(
                ['cup_standing_id' => $standingId],
                [
                    'season_id' => $seasonId,
                    'result' => $title,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
<<<<<<< Updated upstream
    
    /**
     * Cập nhật lịch sử cho group stage
     */
    public function updateGroupStageHistory($teamId, $seasonId, $goalsScored, $goalsConceded, $fouls, $possession)
    {
        if (!$teamId) return;
        
        $history = DB::table('cup_standings')
            ->where('team_id', $teamId)
            ->where('season_id', $seasonId)
            ->first();
        
        if (!$history) return;
        
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
        
        DB::table('cup_standings')->updateOrInsert(
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
    
    /**
     * Cập nhật lịch sử cho tier matches
     */
    public function updateTierHistory($teamId, $seasonId, $goalsScored, $goalsConceded, $fouls, $possession)
    {
        if (!$teamId) return;
        
        $history = DB::table('tier_standings')
            ->where('team_id', $teamId)
            ->where('season_id', $seasonId)
            ->first();
        
        if (!$history) return;
        
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
        
        DB::table('tier_standings')->updateOrInsert(
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
    
    /**
     * Cập nhật bảng xếp hạng
     */
    public function updateStandings($seasonId, $type = 'group')
    {
        if ($type === 'group') {
            $teamsHistory = DB::table('cup_standings')
                ->join('teams', 'cup_standings.team_id', '=', 'teams.id')
                ->where('cup_standings.season_id', $seasonId)
                ->select(
                    'cup_standings.*',
                    DB::raw('COALESCE(teams.form, 0) as team_form')
                )
                ->get()
                ->groupBy('group');
            
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
                    // Update position vào cup_positions
                    $standingId = DB::table('cup_standings')
                        ->where('team_id', $team->team_id)
                        ->where('season_id', $seasonId)
                        ->where('group', $group)
                        ->value('id');
                    
                    if ($standingId) {
                        $seasonIdFromStanding = DB::table('cup_standings')
                            ->where('id', $standingId)
                            ->value('season_id');
                        
                        DB::table('cup_positions')->updateOrInsert(
                            ['cup_standing_id' => $standingId],
                            [
                                'season_id' => $seasonIdFromStanding,
                                'position' => $index + 1,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]
                        );
                    }
                }
            }
        } elseif ($type === 'tier') {
            $teamsHistory = DB::table('tier_standings')
                ->join('teams', 'tier_standings.team_id', '=', 'teams.id')
                ->where('tier_standings.season_id', $seasonId)
                ->select(
                    'tier_standings.*',
                    DB::raw('COALESCE(teams.form, 0) as team_form')
                )
                ->get()
                ->groupBy('tier');
            
            foreach ($teamsHistory as $tier => $tierTeams) {
                $sortedTeams = $tierTeams->sortByDesc(function ($team) {
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
                    // Update position vào tier_positions
                    $standingId = DB::table('tier_standings')
                        ->where('team_id', $team->team_id)
                        ->where('season_id', $seasonId)
                        ->where('tier', $tier)
                        ->value('id');
                    
                    if ($standingId) {
                        $seasonIdFromStanding = DB::table('tier_standings')
                            ->where('id', $standingId)
                            ->value('season_id');
                        
                        DB::table('tier_positions')->updateOrInsert(
                            ['tier_standing_id' => $standingId],
                            [
                                'season_id' => $seasonIdFromStanding,
                                'position' => $index + 1,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]
                        );
                    }
                }
            }
        }
    }
}
=======

    protected function getMatchResult(int $goalsScored, int $goalsConceded): string
    {
        if ($goalsScored > $goalsConceded) {
            return 'win';
        }
        if ($goalsScored < $goalsConceded) {
            return 'lose';
        }
        return 'draw';
    }
}
>>>>>>> Stashed changes
