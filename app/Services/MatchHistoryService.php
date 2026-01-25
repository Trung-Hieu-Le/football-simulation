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
        if (!$teamId) return;
        
        $history = DB::table('group_stage_standings')
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
        
        DB::table('group_stage_standings')->updateOrInsert(
            ['team_id' => $teamId, 'season_id' => $seasonId],
            [
                'match_played' => $matchPlayed,
                'goal_scored' => $goalScored,
                'goal_conceded' => $goalConceded,
                'goal_difference' => $goalDifference,
                'foul' => $totalFouls,
                'average_possession' => round($averagePossession, 2),
                'title' => $title,
                'win' => $win,
                'draw' => $draw,
                'lose' => $lose,
                'updated_at' => now(),
            ]
        );
    }
    
    /**
     * Cập nhật lịch sử cho group stage
     */
    public function updateGroupStageHistory($teamId, $seasonId, $goalsScored, $goalsConceded, $fouls, $possession)
    {
        if (!$teamId) return;
        
        $history = DB::table('group_stage_standings')
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
        
        DB::table('group_stage_standings')->updateOrInsert(
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
            $teamsHistory = DB::table('group_stage_standings')
                ->join('teams', 'group_stage_standings.team_id', '=', 'teams.id')
                ->where('group_stage_standings.season_id', $seasonId)
                ->select(
                    'group_stage_standings.*',
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
                    DB::table('group_stage_standings')
                        ->where('team_id', $team->team_id)
                        ->where('season_id', $seasonId)
                        ->where('group', $group)
                        ->update(['position' => $index + 1]);
                }
            }
        }
    }
}