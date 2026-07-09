<?php

namespace App\Services;

use App\Models\Cup\EliminateMatch;
use App\Models\Cup\Season;
use Illuminate\Support\Collection;

class CupKnockoutService
{
    /**
     * R16 pairing for 8 groups (A–H), top 4 per group → 32 knockout teams.
     * Preserved from original SeasonCupController.
     */
    public const BRANCHES_32 = [
        [['A1', 'B4'], ['D2', 'G3'], ['E1', 'F4'], ['H2', 'C3']],
        [['A4', 'H1'], ['C2', 'F3'], ['D1', 'E4'], ['G2', 'B3']],
        [['C1', 'D4'], ['A3', 'F2'], ['G1', 'H4'], ['B2', 'E3']],
        [['F1', 'G4'], ['A2', 'D3'], ['B1', 'C4'], ['E2', 'H3']],
    ];

    public const ROUND_ORDER = [
        'round_of_16',
        'round_of_8',
        'quarter_finals',
        'semi_finals',
        'third_place',
        'final',
    ];

    /**
     * @param array<string, array<int, array{team_id: int, points: int}>> $groupStandings
     */
    public function populateRoundOf16(Season $season, array $groupStandings): void
    {
        $teamMapping = $this->createTeamMapping($groupStandings);
        $slotIndex = 0;

        foreach (self::BRANCHES_32 as $branchIndex => $branch) {
            foreach ($branch as $matchPair) {
                [$team1Code, $team2Code] = $matchPair;

                EliminateMatch::updateOrCreate(
                    [
                        'season_id' => $season->id,
                        'round' => 'round_of_16',
                        'slot_index' => $slotIndex,
                    ],
                    [
                        'branch' => (string) ($branchIndex + 1),
                        'team1_id' => $teamMapping[$team1Code] ?? null,
                        'team2_id' => $teamMapping[$team2Code] ?? null,
                    ]
                );

                $slotIndex++;
            }
        }
    }

    /**
     * @param array<string, array<int, array{team_id: int, points: int}>> $groupStandings
     * @return array<string, int> e.g. ['A1' => 5, 'B2' => 12]
     */
    protected function createTeamMapping(array $groupStandings): array
    {
        $mapping = [];

        foreach ($groupStandings as $group => $standings) {
            $sorted = collect($standings)->sortByDesc(function ($standing) {
                return [
                    $standing['points'] ?? 0,
                    $standing['goal_difference'] ?? 0,
                    $standing['goal_scored'] ?? 0,
                ];
            })->values();

            foreach ($sorted->take(4) as $position => $standing) {
                $rank = $position + 1;
                $code = strtoupper($group) . $rank;
                $mapping[$code] = $standing['team_id'];
            }
        }

        return $mapping;
    }

    public function updateBracket(Season $season, string $completedRound): void
    {
        if ($completedRound === 'semi_finals') {
            $this->populateFinalAndThirdPlace($season);
            return;
        }

        $nextRound = $this->getNextRound($completedRound);
        if (!$nextRound || in_array($nextRound, ['third_place', 'final'], true)) {
            return;
        }

        $this->advanceWinnersBySlot($season, $completedRound, $nextRound);
    }

    protected function advanceWinnersBySlot(Season $season, string $fromRound, string $toRound): void
    {
        $completed = EliminateMatch::where('season_id', $season->id)
            ->where('round', $fromRound)
            ->whereNotNull('winner_id')
            ->orderBy('slot_index')
            ->get();

        $nextMatches = EliminateMatch::where('season_id', $season->id)
            ->where('round', $toRound)
            ->orderBy('slot_index')
            ->get()
            ->keyBy('slot_index');

        foreach ($completed->groupBy(fn ($m) => (int) floor($m->slot_index / 2)) as $parentSlot => $pair) {
            $winners = $pair->sortBy('slot_index')->values();
            $match = $nextMatches->get($parentSlot);
            if (!$match || $winners->count() < 2) {
                continue;
            }

            $match->update([
                'team1_id' => $winners[0]->winner_id,
                'team2_id' => $winners[1]->winner_id,
            ]);
        }
    }

    protected function populateFinalAndThirdPlace(Season $season): void
    {
        $semiMatches = EliminateMatch::where('season_id', $season->id)
            ->where('round', 'semi_finals')
            ->whereNotNull('winner_id')
            ->orderBy('slot_index')
            ->get();

        if ($semiMatches->count() < 2) {
            return;
        }

        $semiMatches = $semiMatches->take(2);
        $winners = $semiMatches->pluck('winner_id');
        $losers = $semiMatches->map(fn ($m) => $m->getLoserId());

        EliminateMatch::updateOrCreate(
            ['season_id' => $season->id, 'round' => 'third_place', 'slot_index' => 0],
            [
                'branch' => '1',
                'team1_id' => $losers[0],
                'team2_id' => $losers[1],
            ]
        );

        EliminateMatch::updateOrCreate(
            ['season_id' => $season->id, 'round' => 'final', 'slot_index' => 0],
            [
                'branch' => '1',
                'team1_id' => $winners[0],
                'team2_id' => $winners[1],
            ]
        );
    }

    protected function getNextRound(string $currentRound): ?string
    {
        $index = array_search($currentRound, self::ROUND_ORDER, true);
        if ($index === false || $index >= count(self::ROUND_ORDER) - 1) {
            return null;
        }

        return self::ROUND_ORDER[$index + 1];
    }

    public function createKnockoutBracket(Season $season): void
    {
        $structure = [
            'round_of_16' => 16,
            'round_of_8' => 8,
            'quarter_finals' => 4,
            'semi_finals' => 2,
            'third_place' => 1,
            'final' => 1,
        ];

        foreach ($structure as $round => $matchCount) {
            for ($slot = 0; $slot < $matchCount; $slot++) {
                $branch = $round === 'round_of_16'
                    ? (string) (floor($slot / 4) + 1)
                    : '1';

                EliminateMatch::updateOrCreate(
                    [
                        'season_id' => $season->id,
                        'round' => $round,
                        'slot_index' => $slot,
                    ],
                    [
                        'branch' => $branch,
                        'team1_id' => null,
                        'team2_id' => null,
                    ]
                );
            }
        }
    }

    /**
     * @return Collection<string, Collection<int, EliminateMatch>>
     */
    public function getBracketGroupedByRound(int $seasonId): Collection
    {
        return EliminateMatch::where('season_id', $seasonId)
            ->with(['team1', 'team2', 'winner'])
            ->orderByRaw("FIELD(round, '" . implode("','", self::ROUND_ORDER) . "')")
            ->orderBy('slot_index')
            ->get()
            ->groupBy('round');
    }
}
