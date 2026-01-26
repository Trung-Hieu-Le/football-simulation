<?php

namespace App\Http\Controllers\Cup;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Services\MatchSimulationService;
use App\Services\MatchHistoryService;

class MatchCupController extends Controller
{
    protected $simulationService;
    protected $historyService;
    
    public function __construct(
        MatchSimulationService $simulationService,
        MatchHistoryService $historyService
    ) {
        $this->simulationService = $simulationService;
        $this->historyService = $historyService;
    }
    
    public function simulateMatch(Request $request)
    {
        $season_id = $request->input('season_id');
        $match_count = $request->input('match_count', 1);
        $season_meta = DB::table('cup_seasons')->where('id', $season_id)->value('meta');
        
        $nextMatches = DB::table('cup_group_stage_matches')
            ->where('season_id', $season_id)
            ->whereNull('team1_score')
            ->whereNull('team2_score')
            ->limit($match_count)
            ->get();
        
        if($nextMatches->isEmpty()) {
            return redirect()->back()->with('fail', 'No match to simulate!');
        }
        
        $matchResult = [];
        
        foreach ($nextMatches as $match) {
            $team1 = DB::table('teams')->where('id', $match->team1_id)->first();
            $team2 = DB::table('teams')->where('id', $match->team2_id)->first();
            
            if (!$team1 || !$team2) {
                return redirect()->back()->with('fail', 'Team not found!');
            }
            
            // Khởi tạo match data
            $matchData = [
                'team1_score' => 0,
                'team2_score' => 0,
                'team1_fouls' => 0,
                'team2_fouls' => 0,
                'team1_possession' => 0,
                'team2_possession' => 0,
                'team1_shots' => 0,
                'team1_shots_on_target' => 0,
                'team2_shots' => 0,
                'team2_shots_on_target' => 0,
                'specialEvents' => [],
                'specialEvents' => []
            ];
            
            // Simulate fulltime
            $this->simulationService->simulateFullTime($team1, $team2, $season_meta, $matchData);
            $possessionTotal = $matchData['team1_possession'] + $matchData['team2_possession'];
            $possessionTeam1 = round(($matchData['team1_possession'] / $possessionTotal) * 100, 2);
            $possessionTeam2 = round(($matchData['team2_possession'] / $possessionTotal) * 100, 2);

            // Cập nhật match
            DB::table('cup_group_stage_matches')
                ->where('id', $match->id)
                ->update([
                    'team1_score' => $matchData['team1_score'],
                    'team2_score' => $matchData['team2_score'],
                    'team1_possession' => $possessionTeam1,
                    'team2_possession' => $possessionTeam2,
                    'team1_foul' => $matchData['team1_fouls'],
                    'team2_foul' => $matchData['team2_fouls'],
                    'updated_at' => now()
                ]);
            
            // Cập nhật history
            $this->historyService->updateGroupStageHistory(
                $match->team1_id, $match->season_id, 
                $matchData['team1_score'], $matchData['team2_score'],
                $matchData['team1_fouls'], $possessionTeam1
            );
            $this->historyService->updateGroupStageHistory(
                $match->team2_id, $match->season_id,
                $matchData['team2_score'], $matchData['team1_score'],
                $matchData['team2_fouls'], $possessionTeam2
            );
            
            // Cập nhật standings
            $this->historyService->updateStandings($match->season_id, 'group');
            
            // Lưu match result
            $matchResult = [
                'team1_name' => $team1->name,
                'team2_name' => $team2->name,
                'team1_score' => $matchData['team1_score'],
                'team2_score' => $matchData['team2_score'],
                'team1_shots' => $matchData['team1_shots'],
                'team2_shots' => $matchData['team2_shots'],
                'team1_shots_on_target' => $matchData['team1_shots_on_target'],
                'team2_shots_on_target' => $matchData['team2_shots_on_target'],
                'team1_possession' => $possessionTeam1,
                'team2_possession' => $possessionTeam2,
                'specialEvents' => $matchData['specialEvents'] ?? [],
            ];
        }
        
        return redirect()->back()
            ->with('success', 'Next matches simulated successfully!')
            ->with('matchResult', $matchResult);
    }
}