<?php

namespace App\Services;

use App\Models\Cup\EliminateMatch;
use App\Models\Cup\Season;
use Illuminate\Support\Collection;

class CupKnockoutService
{
    /**
     * Branch pairing formula for 32 teams (8 groups)
     * Preserved from original SeasonCupController.php:430-435
     */
    public const BRANCHES_32 = [
        [['A1', 'B4'], ['D2', 'G3'], ['E1', 'F4'], ['H2', 'C3']],
        [['A4', 'H1'], ['C2', 'F3'], ['D1', 'E4'], ['G2', 'B3']],
        [['C1', 'D4'], ['A3', 'F2'], ['G1', 'H4'], ['B2', 'E3']],
        [['F1', 'G4'], ['A2', 'D3'], ['B1', 'C4'], ['E2', 'H3']],
    ];

    /**
     * Generate round of 16 matches based on group stage results
     * @param Season $season
     * @param array $groupStandings [group => [team_id => standing]]
     * @return Collection Collection of EliminateMatch models
     */
    public function generateRoundOf16(Season $season, array $groupStandings): Collection
    {
        $teamMapping = $this->createTeamMapping($groupStandings);
        $matches = collect();

        foreach (self::BRANCHES_32 as $branchIndex => $branch) {
            foreach ($branch as $matchIndex => $matchPair) {
                [$team1Code, $team2Code] = $matchPair;
                
                $team1Id = $teamMapping[$team1Code] ?? null;
                $team2Id = $teamMapping[$team2Code] ?? null;

                $match = new EliminateMatch([
                    'season_id' => $season->id,
                    'round' => 'round_of_16',
                    'branch' => (string)($branchIndex + 1),
                    'team1_id' => $team1Id,
                    'team2_id' => $team2Id,
                ]);
                
                $matches->push($match);
            }
        }

        return $matches;
    }

    /**
     * Update knockout bracket after matches are played
     * Automatically advances winners to next round
     * @param Season $season
     * @param string $completedRound
     */
    public function updateBracket(Season $season, string $completedRound): void
    {
        $nextRound = $this->getNextRound($completedRound);
        
        if (!$nextRound) {
            return; // Final completed
        }

        $completedMatches = EliminateMatch::where('season_id', $season->id)
            ->where('round', $completedRound)
            ->whereNotNull('winner_id')
            ->orderBy('branch')
            ->get();

        if ($nextRound === 'final' || $nextRound === 'third_place') {
            $this->generateFinalMatches($season, $completedMatches);
        } else {
            $this->advanceWinnersToNextRound($season, $completedMatches, $nextRound);
        }
    }

    /**
     * Create team mapping from group standings (A1, B2, etc. -> team_id)
     */
    protected function createTeamMapping(array $groupStandings): array
    {
        $mapping = [];

        foreach ($groupStandings as $group => $standings) {
            $sortedStandings = collect($standings)->sortByDesc('points')->values();
            
            foreach ($sortedStandings as $position => $standing) {
                $rank = $position + 1;
                $code = strtoupper($group) . $rank;
                $mapping[$code] = $standing['team_id'];
            }
        }

        return $mapping;
    }

    /**
     * Advance winners from completed round to next round
     */
    protected function advanceWinnersToNextRound(Season $season, Collection $completedMatches, string $nextRound): void
    {
        $nextRoundMatches = EliminateMatch::where('season_id', $season->id)
            ->where('round', $nextRound)
            ->orderBy('branch')
            ->get();

        $winners = $completedMatches->pluck('winner_id')->chunk(2);

        foreach ($winners as $index => $winnerPair) {
            if (isset($nextRoundMatches[$index])) {
                $match = $nextRoundMatches[$index];
                $match->team1_id = $winnerPair[0] ?? null;
                $match->team2_id = $winnerPair[1] ?? null;
                $match->save();
            }
        }
    }

    /**
     * Generate final and third place matches from semi-final results
     */
    protected function generateFinalMatches(Season $season, Collection $semiMatches): void
    {
        if ($semiMatches->count() < 2) {
            return;
        }

        $winners = $semiMatches->pluck('winner_id');
        $losers = $semiMatches->map(fn($m) => $m->getLoserId());

        EliminateMatch::updateOrCreate(
            [
                'season_id' => $season->id,
                'round' => 'final',
            ],
            [
                'branch' => '1',
                'team1_id' => $winners[0] ?? null,
                'team2_id' => $winners[1] ?? null,
            ]
        );

        EliminateMatch::updateOrCreate(
            [
                'season_id' => $season->id,
                'round' => 'third_place',
            ],
            [
                'branch' => '1',
                'team1_id' => $losers[0] ?? null,
                'team2_id' => $losers[1] ?? null,
            ]
        );
    }

    /**
     * Get next round name
     */
    protected function getNextRound(string $currentRound): ?string
    {
        $rounds = [
            'round_of_16' => 'quarter_finals',
            'quarter_finals' => 'semi_finals',
            'semi_finals' => 'final',
        ];

        return $rounds[$currentRound] ?? null;
    }

    /**
     * Create initial knockout bracket structure (empty matches)
     */
    public function createKnockoutBracket(Season $season, int $startingTeamCount): void
    {
        $rounds = $this->getRoundsForTeamCount($startingTeamCount);

        foreach ($rounds as $round => $matchCount) {
            for ($i = 1; $i <= $matchCount; $i++) {
                EliminateMatch::create([
                    'season_id' => $season->id,
                    'round' => $round,
                    'branch' => (string)ceil($i / ($matchCount / 4)),
                    'team1_id' => null,
                    'team2_id' => null,
                ]);
            }
        }
    }

    /**
     * Get rounds configuration based on team count
     */
    protected function getRoundsForTeamCount(int $teamCount): array
    {
        if ($teamCount === 32) {
            return [
                'round_of_16' => 16,
                'quarter_finals' => 8,
                'semi_finals' => 4,
                'final' => 1,
                'third_place' => 1,
            ];
        } elseif ($teamCount === 64) {
            return [
                'round_of_32' => 32,
                'round_of_16' => 16,
                'quarter_finals' => 8,
                'semi_finals' => 4,
                'final' => 1,
                'third_place' => 1,
            ];
        }

        throw new \InvalidArgumentException("Unsupported team count: {$teamCount}");
    }
}
