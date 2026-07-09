<?php

namespace App\Services;

class RoundRobinService
{
    /**
     * Single round-robin schedule (circle method).
     *
     * @param array<int|string> $teamIds Team IDs in pot order (index 0 = pot1, etc.)
     * @return array<int, array<int, array{0: int|string, 1: int|string}>> Round => list of [team1, team2]
     */
    public function generateSingleRoundRobin(array $teamIds): array
    {
        $teams = array_values($teamIds);
        $n = count($teams);

        if ($n < 2) {
            return [];
        }

        if ($n % 2 !== 0) {
            $teams[] = null;
            $n++;
        }

        $fixed = array_pop($teams);
        $rotating = $teams;
        $rounds = [];

        for ($round = 0; $round < $n - 1; $round++) {
            $pairings = array_merge([$fixed], $rotating);
            $roundMatches = [];

            for ($i = 0; $i < $n / 2; $i++) {
                $home = $pairings[$i];
                $away = $pairings[$n - 1 - $i];

                if ($home !== null && $away !== null) {
                    $roundMatches[] = [$home, $away];
                }
            }

            $rounds[$round + 1] = $roundMatches;

            $last = array_pop($rotating);
            array_unshift($rotating, $last);
        }

        return $rounds;
    }

    /** @return array<int, array{0: int, 1: int}> */
    public function countMatchesPerGroup(int $teamCount): array
    {
        $matchesPerTeam = $teamCount - 1;
        $totalMatches = ($teamCount * $matchesPerTeam) / 2;

        return [
            'rounds' => $matchesPerTeam,
            'matches' => (int) $totalMatches,
        ];
    }
}
