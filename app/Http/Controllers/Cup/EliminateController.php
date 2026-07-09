<?php

namespace App\Http\Controllers\Cup;

use App\Http\Controllers\Controller;
use App\Models\Cup\Season;
use App\Models\Cup\EliminateMatch;
use App\Services\Simulation\MatchSimulator;
use App\Services\Simulation\PenaltyShootoutService;
use App\Services\MatchHistoryService;
use App\Services\CupKnockoutService;
use Illuminate\Http\Request;

class EliminateController extends Controller
{
    public function __construct(
        protected MatchSimulator $matchSimulator,
        protected PenaltyShootoutService $penaltyService,
        protected MatchHistoryService $historyService,
        protected CupKnockoutService $knockoutService,
    ) {
    }

    public function index($seasonId)
    {
        $season = Season::findOrFail($seasonId);
        $matches = $this->knockoutService->getBracketGroupedByRound($seasonId);
        $roundOrder = CupKnockoutService::ROUND_ORDER;

        return view('cup.eliminate.index', compact('season', 'matches', 'roundOrder'));
    }

    public function simulateRound($seasonId, $round)
    {
        $season = Season::findOrFail($seasonId);

        $matches = EliminateMatch::where('season_id', $seasonId)
            ->where('round', $round)
            ->whereNull('winner_id')
            ->orderBy('slot_index')
            ->with(['team1', 'team2'])
            ->get();

        foreach ($matches as $match) {
            if ($match->team1_id && $match->team2_id) {
                $this->simulateMatch($match, $season->meta);
            }
        }

        $this->knockoutService->updateBracket($season, $round);

        return redirect()->route('cup.eliminate.index', $seasonId)
            ->with('success', ucfirst(str_replace('_', ' ', $round)) . ' simulated successfully!');
    }

    protected function simulateMatch(EliminateMatch $match, string $seasonMeta): void
    {
        $result = $this->matchSimulator->simulateMatch(
            $match->team1,
            $match->team2,
            $seasonMeta,
            true
        );

        if ($result['team1_score'] === $result['team2_score']) {
            $penaltyResult = $this->penaltyService->simulatePenaltyShootout(
                $match->team1,
                $match->team2
            );
            $winnerId = $penaltyResult['winner'] === 1 ? $match->team1_id : $match->team2_id;
        } else {
            $winnerId = $result['team1_score'] > $result['team2_score']
                ? $match->team1_id
                : $match->team2_id;
        }

        $match->update([
            'team1_score' => $result['team1_score'],
            'team2_score' => $result['team2_score'],
            'team1_possession' => $result['team1_possession'],
            'team2_possession' => $result['team2_possession'],
            'team1_foul' => $result['team1_fouls'],
            'team2_foul' => $result['team2_fouls'],
            'winner_id' => $winnerId,
        ]);

        $this->historyService->updateCupEliminateHistory(
            $match->team1_id,
            $match->team2_id,
            $match->season_id,
            $result['team1_score'],
            $result['team2_score'],
            $result['team1_fouls'],
            $result['team2_fouls'],
            $result['team1_possession'],
            $result['team2_possession'],
            $match->round,
            $winnerId
        );
    }

    public function show($matchId)
    {
        $match = EliminateMatch::with(['team1', 'team2', 'winner', 'season'])
            ->findOrFail($matchId);

        return view('cup.eliminate.show', compact('match'));
    }
}
