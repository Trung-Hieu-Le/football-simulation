<?php

namespace App\Http\Controllers\Cup;

use App\Http\Controllers\Controller;
use App\Models\Cup\Season;
use App\Models\Cup\GroupStageMatch;
use App\Services\Simulation\MatchSimulator;
use App\Services\MatchHistoryService;
use Illuminate\Http\Request;

class MatchController extends Controller
{
    protected MatchSimulator $matchSimulator;
    protected MatchHistoryService $historyService;

    public function __construct(MatchSimulator $matchSimulator, MatchHistoryService $historyService)
    {
        $this->matchSimulator = $matchSimulator;
        $this->historyService = $historyService;
    }

    public function index($seasonId)
    {
        $season = Season::findOrFail($seasonId);
        
        $matches = GroupStageMatch::where('season_id', $seasonId)
                                  ->with(['team1', 'team2'])
                                  ->orderBy('group')
                                  ->orderBy('round')
                                  ->get()
                                  ->groupBy(['group', 'round']);

        return view('cup.matches.index', compact('season', 'matches'));
    }

    public function simulate($seasonId, $round)
    {
        $season = Season::findOrFail($seasonId);
        
        $matches = GroupStageMatch::where('season_id', $seasonId)
                                  ->where('round', $round)
                                  ->where('team1_score', 0)
                                  ->where('team2_score', 0)
                                  ->with(['team1', 'team2'])
                                  ->get();

        foreach ($matches as $match) {
            $this->simulateMatch($match, $season->meta);
        }

        return redirect()->route('cup.matches.index', $seasonId)
                        ->with('success', "Round {$round} simulated successfully!");
    }

    public function simulateAll($seasonId)
    {
        $season = Season::findOrFail($seasonId);
        
        $matches = GroupStageMatch::where('season_id', $seasonId)
                                  ->where('team1_score', 0)
                                  ->where('team2_score', 0)
                                  ->with(['team1', 'team2'])
                                  ->get();

        foreach ($matches as $match) {
            $this->simulateMatch($match, $season->meta);
        }

        return redirect()->route('cup.seasons.show', $seasonId)
                        ->with('success', 'All group stage matches simulated successfully!');
    }

    protected function simulateMatch(GroupStageMatch $match, string $seasonMeta): void
    {
        $result = $this->matchSimulator->simulateMatch(
            $match->team1,
            $match->team2,
            $seasonMeta,
            false
        );

        $match->update([
            'team1_score' => $result['team1_score'],
            'team2_score' => $result['team2_score'],
            'team1_possession' => $result['team1_possession'],
            'team2_possession' => $result['team2_possession'],
            'team1_foul' => $result['team1_fouls'],
            'team2_foul' => $result['team2_fouls'],
        ]);

        $this->historyService->updateCupGroupStageHistory(
            $match->team1_id,
            $match->team2_id,
            $match->season_id,
            $result['team1_score'],
            $result['team2_score'],
            $result['team1_fouls'],
            $result['team2_fouls'],
            $result['team1_possession'],
            $result['team2_possession']
        );
    }

    public function show($matchId)
    {
        $match = GroupStageMatch::with(['team1', 'team2', 'season'])
                                ->findOrFail($matchId);
        
        return view('cup.matches.show', compact('match'));
    }
}
