<?php

namespace App\Services;

use App\Models\Team;
use Illuminate\Support\Collection;

class CupPotSeedingService
{
    public const POT_COUNT = 4;

    /**
     * Distribute teams into 4 pots based on ELO ranking
     * @param Collection $teams Collection of Team models
     * @return array [pot1 => [...], pot2 => [...], pot3 => [...], pot4 => [...]]
     */
    public function distributeToPots(Collection $teams): array
    {
        $teamsCount = $teams->count();
        if (!in_array($teamsCount, [32, 64])) {
            throw new \InvalidArgumentException("Cup mode requires 32 or 64 teams, got {$teamsCount}");
        }

        $sortedTeams = $teams->sortByDesc('elo')->values();
        $teamsPerPot = (int)($teamsCount / self::POT_COUNT);

        $pots = [
            'pot1' => [],
            'pot2' => [],
            'pot3' => [],
            'pot4' => [],
        ];

        foreach ($sortedTeams as $index => $team) {
            $potNumber = (int)floor($index / $teamsPerPot) + 1;
            $potNumber = min($potNumber, self::POT_COUNT);
            $pots["pot{$potNumber}"][] = $team;
        }

        return $pots;
    }

    /**
     * Draw teams into groups from pots
     * @param array $pots [pot1 => [...], pot2 => [...], pot3 => [...], pot4 => [...]]
     * @param int $groupCount Number of groups (8 for 32 teams, 16 for 64 teams)
     * @return array [A => [...], B => [...], ...]
     */
    public function drawGroups(array $pots, int $groupCount): array
    {
        $groups = [];
        $groupLetters = range('A', chr(65 + $groupCount - 1));

        foreach ($groupLetters as $letter) {
            $groups[$letter] = [];
        }

        foreach ($pots as $potName => $potTeams) {
            shuffle($potTeams);
            
            foreach ($potTeams as $index => $team) {
                $groupIndex = $index % $groupCount;
                $groupLetter = $groupLetters[$groupIndex];
                $groups[$groupLetter][] = $team;
            }
        }

        return $groups;
    }

    /**
     * Get top teams from each group for knockout stage
     * @param array $groupStandings [groupName => [standings sorted by rank]]
     * @param int $teamsPerGroup Number of teams to advance per group
     * @return Collection Collection of team IDs
     */
    public function getKnockoutTeams(array $groupStandings, int $teamsPerGroup = 2): Collection
    {
        $qualifiedTeams = collect();

        foreach ($groupStandings as $groupName => $standings) {
            $topTeams = collect($standings)->take($teamsPerGroup);
            $qualifiedTeams = $qualifiedTeams->concat($topTeams->pluck('team_id'));
        }

        return $qualifiedTeams;
    }
}
