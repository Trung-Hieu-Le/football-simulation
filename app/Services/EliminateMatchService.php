<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class EliminateMatchService
{
    /**
     * Lấy title theo round
     */
    public function getTitleByRound($round, $teamId, $winnerId)
    {
        if ($round === 'final') {
            return $teamId === $winnerId ? 'champion' : 'runner_up';
        } elseif ($round === 'third_place') {
            return $teamId === $winnerId ? '3rd_place' : '4th_place';
        } else {
            return $round;
        }
    }
    
    /**
     * Thêm đội vào trận đấu tiếp theo
     */
    public function addTeamToMatch($seasonId, $round, $teamId)
    {
        $match = DB::table('cup_eliminate_stage_matches')
            ->where('season_id', $seasonId)
            ->where('round', $round)
            ->where(function ($query) {
                $query->whereNull('team1_id')
                    ->orWhereNull('team2_id');
            })
            ->first();
        
        if ($match) {
            $updateData = [];
            if (is_null($match->team1_id)) {
                $updateData['team1_id'] = $teamId;
            } elseif (is_null($match->team2_id)) {
                $updateData['team2_id'] = $teamId;
            }
            
            DB::table('cup_eliminate_stage_matches')
                ->where('id', $match->id)
                ->update($updateData);
        }
    }
    
    /**
     * Xử lý trận đấu tiếp theo sau khi có kết quả
     */
    public function handleNextMatch($seasonId, $round, $winnerId, $loserId)
    {
        if ($round === 'semi_finals') {
            $this->addTeamToMatch($seasonId, 'third_place', $loserId);
            $this->addTeamToMatch($seasonId, 'final', $winnerId);
        } else {
            $next_match = DB::table('cup_eliminate_stage_matches')
                ->where('season_id', $seasonId)
                ->where(function ($query) {
                    $query->whereNull('team1_id')
                          ->orWhereNull('team2_id');
                })
                ->first();
            
            if ($next_match) {
                $update_data = [];
                if (is_null($next_match->team1_id)) {
                    $update_data['team1_id'] = $winnerId;
                } elseif (is_null($next_match->team2_id)) {
                    $update_data['team2_id'] = $winnerId;
                }
                DB::table('cup_eliminate_stage_matches')
                    ->where('id', $next_match->id)
                    ->update($update_data);
            }
        }
    }
}