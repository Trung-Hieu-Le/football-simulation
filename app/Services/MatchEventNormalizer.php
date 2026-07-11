<?php

namespace App\Services;

class MatchEventNormalizer
{
    public function buildFromSimulation(array $simulationResult, ?array $penaltyShootout = null): array
    {
        return [
            'goals' => $simulationResult['goals'] ?? [],
            'penalty_shootout' => $penaltyShootout,
        ];
    }

    public function buildPenaltyShootout(
        int $team1Id,
        int $team2Id,
        array $penaltyResult
    ): array {
        $team1PenaltyScore = (int) ($penaltyResult['team1_penalty_score'] ?? 0);
        $team2PenaltyScore = (int) ($penaltyResult['team2_penalty_score'] ?? 0);
        $winnerTeamId = ($penaltyResult['winner'] ?? 1) === 1 ? $team1Id : $team2Id;

        return [
            'decided_by_penalties' => true,
            'team1_id' => $team1Id,
            'team2_id' => $team2Id,
            'team1_penalty_score' => $team1PenaltyScore,
            'team2_penalty_score' => $team2PenaltyScore,
            'winner_team_id' => $winnerTeamId,
            'kicks' => $penaltyResult['kicks'] ?? [],
        ];
    }
}
