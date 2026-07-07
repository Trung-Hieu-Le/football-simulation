<?php

namespace App\Services;

use App\Models\Team;

class EloRatingService
{
    public const K_FACTOR = 32;
    public const DEFAULT_ELO = 1000;

    /**
     * Calculate expected score for a team
     */
    public function calculateExpectedScore(int $eloA, int $eloB): float
    {
        return 1 / (1 + pow(10, ($eloB - $eloA) / 400));
    }

    /**
     * Calculate ELO change after a match
     * @param int $eloA Team A's current ELO
     * @param int $eloB Team B's current ELO
     * @param float $actualScore Team A's actual score (1.0 = win, 0.5 = draw, 0.0 = loss)
     * @return int ELO change for Team A (positive or negative)
     */
    public function calculateEloChange(int $eloA, int $eloB, float $actualScore): int
    {
        $expectedScore = $this->calculateExpectedScore($eloA, $eloB);
        $change = self::K_FACTOR * ($actualScore - $expectedScore);
        
        return (int)round($change);
    }

    /**
     * Update ELO ratings for both teams after a match
     * @param Team $team1
     * @param Team $team2
     * @param int $team1Score Match score for team 1
     * @param int $team2Score Match score for team 2
     */
    public function updateEloAfterMatch(Team $team1, Team $team2, int $team1Score, int $team2Score): void
    {
        $actualScore = $this->getActualScore($team1Score, $team2Score);
        
        $team1Change = $this->calculateEloChange($team1->elo, $team2->elo, $actualScore);
        $team2Change = -$team1Change;

        $team1->updateElo($team1Change);
        $team2->updateElo($team2Change);
    }

    /**
     * Get actual score from match result (1.0 = win, 0.5 = draw, 0.0 = loss)
     */
    protected function getActualScore(int $team1Score, int $team2Score): float
    {
        if ($team1Score > $team2Score) {
            return 1.0;
        } elseif ($team1Score < $team2Score) {
            return 0.0;
        }
        return 0.5;
    }

    /**
     * Reset team ELO to default
     */
    public function resetTeamElo(Team $team): void
    {
        $team->elo = self::DEFAULT_ELO;
        $team->save();
    }

    /**
     * Reset all teams ELO to default
     */
    public function resetAllTeamsElo(): void
    {
        Team::query()->update(['elo' => self::DEFAULT_ELO]);
    }
}
