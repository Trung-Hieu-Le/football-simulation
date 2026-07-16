<?php

namespace App\Services;

use App\Enums\DivisionLevel;
use App\Models\Team;

class EloRatingService
{
    public const K_FACTOR = 32;
    public const DEFAULT_ELO = 1000;

    public function calculateExpectedScore(int $eloA, int $eloB): float
    {
        return 1 / (1 + pow(10, ($eloB - $eloA) / 400));
    }

    public function calculateEloChange(int $eloA, int $eloB, float $actualScore): int
    {
        $expectedScore = $this->calculateExpectedScore($eloA, $eloB);
        $change = self::K_FACTOR * ($actualScore - $expectedScore);

        return (int) round($change);
    }

    public function updateEloAfterMatch(
        Team $team1,
        Team $team2,
        int $team1Score,
        int $team2Score,
        ?string $division = null
    ): void {
        $actualScore = $this->getActualScore($team1Score, $team2Score);

        $team1Change = $this->calculateEloChange($team1->elo, $team2->elo, $actualScore);
        $team2Change = -$team1Change;

        $team1->updateElo($this->scaleByDivision($team1Change, $division));
        $team2->updateElo($this->scaleByDivision($team2Change, $division));
    }

    public function scaleByDivision(float $change, ?string $division): int
    {
        [$gainMult, $lossMult] = match ($division) {
            DivisionLevel::DIVISION1->value => [1.3, 0.7],
            DivisionLevel::DIVISION3->value => [0.7, 1.3],
            default => [1.0, 1.0],
        };

        $mult = $change >= 0 ? $gainMult : $lossMult;

        return (int) round($change * $mult);
    }

    protected function getActualScore(int $team1Score, int $team2Score): float
    {
        if ($team1Score > $team2Score) {
            return 1.0;
        }
        if ($team1Score < $team2Score) {
            return 0.0;
        }

        return 0.5;
    }

    public function resetTeamElo(Team $team): void
    {
        $team->elo = self::DEFAULT_ELO;
        $team->save();
    }

    public function resetAllTeamsElo(): void
    {
        Team::query()->update(['elo' => self::DEFAULT_ELO]);
    }
}
