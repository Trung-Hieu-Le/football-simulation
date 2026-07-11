<?php

namespace App\Http\Controllers\League;

use App\Http\Controllers\Controller;
use App\Models\League\Season;
use App\Models\League\LeagueMatch;
use App\Models\Team;
use App\Services\Simulation\MatchSimulator;
use App\Services\MatchHistoryService;
use App\Services\MatchEventNormalizer;
use App\Services\LeagueSeasonResultService;
use Illuminate\Http\Request;

class MatchController extends Controller
{
    public function __construct(
        MatchSimulator $matchSimulator,
        MatchHistoryService $historyService,
        MatchEventNormalizer $eventNormalizer,
        LeagueSeasonResultService $resultService,
    ) {
        $this->matchSimulator = $matchSimulator;
        $this->historyService = $historyService;
        $this->eventNormalizer = $eventNormalizer;
        $this->resultService = $resultService;
    }

    protected MatchSimulator $matchSimulator;
    protected MatchHistoryService $historyService;
    protected MatchEventNormalizer $eventNormalizer;
    protected LeagueSeasonResultService $resultService;

    public function index($seasonId)
    {
        $season = Season::findOrFail($seasonId);
        $matches = LeagueMatch::where('season_id', $seasonId)
                       ->with(['team1', 'team2'])
                       ->orderBy('division')
                       ->orderBy('round')
                       ->get()
                       ->groupBy(['division', 'round']);

        return view('league.matches.index', compact('season', 'matches'));
    }

    public function simulate($seasonId, $round)
    {
        $season = Season::findOrFail($seasonId);
        
        $matches = LeagueMatch::where('season_id', $seasonId)
                       ->where('round', $round)
                       ->whereNull('team1_score')
                       ->with(['team1', 'team2'])
                       ->get();

        foreach ($matches as $match) {
            $this->simulateMatch($match, $season->meta);
        }

        $this->resultService->calculateSeasonResults($season);

        return redirect()->route('league.matches.index', $seasonId)
                        ->with('success', "Round {$round} simulated successfully!");
    }

    public function simulateAll($seasonId)
    {
        $season = Season::findOrFail($seasonId);
        
        $matches = LeagueMatch::where('season_id', $seasonId)
                       ->whereNull('team1_score')
                       ->with(['team1', 'team2'])
                       ->get();

        foreach ($matches as $match) {
            $this->simulateMatch($match, $season->meta);
        }

        $this->resultService->calculateSeasonResults($season);

        return redirect()->route('league.seasons.show', $seasonId)
                        ->with('success', 'All matches simulated successfully!');
    }

    protected function simulateMatch(LeagueMatch $match, string $seasonMeta): void
    {
        $result = $this->matchSimulator->simulateMatch(
            $match->team1,
            $match->team2,
            $seasonMeta,
            false
        );

        $matchEvents = $this->eventNormalizer->buildFromSimulation($result);

        $match->update([
            'team1_score' => $result['team1_score'],
            'team2_score' => $result['team2_score'],
            'team1_possession' => $result['team1_possession'],
            'team2_possession' => $result['team2_possession'],
            'team1_foul' => $result['team1_fouls'],
            'team2_foul' => $result['team2_fouls'],
            'match_events' => $matchEvents,
        ]);

        $this->historyService->updateLeagueMatchHistory(
            $match->team1_id,
            $match->team2_id,
            $match->season_id,
            $match->division,
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
        $match = LeagueMatch::with(['team1', 'team2', 'season'])->findOrFail($matchId);
        
        return view('league.matches.show', compact('match'));
    }

    public function reset($seasonId, $round)
    {
        LeagueMatch::where('season_id', $seasonId)
             ->where('round', $round)
             ->update([
                 'team1_score' => null,
                 'team2_score' => null,
                 'team1_possession' => 50,
                 'team2_possession' => 50,
                 'team1_foul' => 0,
                 'team2_foul' => 0,
             ]);

        return redirect()->route('league.matches.index', $seasonId)
                        ->with('success', "Round {$round} reset successfully!");
    }
}
