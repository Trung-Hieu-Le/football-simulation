<?php

namespace App\Services;

use App\Models\Team;
use Illuminate\Support\Collection;

class CupPotSeedingService
{
    public const GROUP_COUNT = 8;

    /**
     * @return array<int, Collection<int, Team>> pots keyed 1..N
     */
    public function distributeToPots(Collection $teams): array
    {
        $teamsCount = $teams->count();
        if (!in_array($teamsCount, [32, 64])) {
            throw new \InvalidArgumentException("Cup mode requires 32 or 64 teams, got {$teamsCount}");
        }

        $teamsPerGroup = (int) ($teamsCount / self::GROUP_COUNT);
        $sortedTeams = $teams->sortByDesc('elo')->values();

        $pots = [];
        for ($pot = 1; $pot <= $teamsPerGroup; $pot++) {
            $start = ($pot - 1) * self::GROUP_COUNT;
            $pots[$pot] = $sortedTeams->slice($start, self::GROUP_COUNT)->values();
        }

        return $pots;
    }

    /**
     * Draw teams into groups A–H: each group gets one team from each pot.
     *
     * @param array<int, Collection<int, Team>> $pots
     * @return array<string, Collection<int, Team>> e.g. ['A' => Collection, ...]
     */
    public function drawGroups(array $pots): array
    {
        $groupLetters = range('A', chr(65 + self::GROUP_COUNT - 1));
        $groups = [];
        foreach ($groupLetters as $letter) {
            $groups[$letter] = collect();
        }

        foreach ($pots as $potTeams) {
            $shuffled = $potTeams->shuffle()->values();
            foreach ($shuffled as $index => $team) {
                $groupLetter = $groupLetters[$index];
                $groups[$groupLetter]->push($team);
            }
        }

        return $groups;
    }

    public function getTeamsPerGroup(int $teamCount): int
    {
        return (int) ($teamCount / self::GROUP_COUNT);
    }
}
