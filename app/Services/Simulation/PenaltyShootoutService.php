<?php

namespace App\Services\Simulation;

class PenaltyShootoutService extends BaseSimulationService
{
    public function simulatePenaltyShootout($team1, $team2): array
    {
        $team1Score = 0;
        $team2Score = 0;
        $kicks = [];

        for ($round = 1; $round <= 5; $round++) {
            $remainingShots = 6 - $round;
            $phase = 'initial';

            $team2Scored = $this->takePenaltyShot($team2, $team1);
            $kicks[] = $this->makeKick($round, $phase, $team2->id, $team2Scored);
            $team2Score += $team2Scored;

            if (abs($team1Score - $team2Score) > $remainingShots) {
                break;
            }

            $team1Scored = $this->takePenaltyShot($team1, $team2);
            $kicks[] = $this->makeKick($round, $phase, $team1->id, $team1Scored);
            $team1Score += $team1Scored;

            if (abs($team1Score - $team2Score) > ($remainingShots - 1)) {
                break;
            }
        }

        $round = 6;
        while ($team1Score === $team2Score) {
            $phase = 'sudden_death';

            $team2Scored = $this->takePenaltyShot($team2, $team1);
            $kicks[] = $this->makeKick($round, $phase, $team2->id, $team2Scored);
            $team2Score += $team2Scored;

            $team1Scored = $this->takePenaltyShot($team1, $team2);
            $kicks[] = $this->makeKick($round, $phase, $team1->id, $team1Scored);
            $team1Score += $team1Scored;

            $round++;
        }

        return [
            'team1_penalty_score' => $team1Score,
            'team2_penalty_score' => $team2Score,
            'winner' => $team1Score > $team2Score ? 1 : 2,
            'kicks' => $kicks,
        ];
    }

    protected function makeKick(int $round, string $phase, int $teamId, int $scored): array
    {
        return [
            'round' => $round,
            'phase' => $phase,
            'team_id' => $teamId,
            'scored' => (bool) $scored,
        ];
    }

    protected function takePenaltyShot($attacking, $defending): int
    {
        $shooterStats = [
            'attack' => $attacking->attack ?? 50,
            'mental' => $attacking->mental ?? 50,
        ];
        
        $keeperStats = [
            'goalkeeping' => $defending->goalkeeping ?? 50,
            'mental' => $defending->mental ?? 50,
        ];
        
        $result = PenaltyCalculator::attempt($shooterStats, $keeperStats, PenaltyCalculator::CONTEXT_SHOOTOUT);
        
        return $result['success'] ? 1 : 0;
    }
}
