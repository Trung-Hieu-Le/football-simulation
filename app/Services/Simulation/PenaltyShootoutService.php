<?php

namespace App\Services\Simulation;

class PenaltyShootoutService extends BaseSimulationService
{
    public function simulatePenaltyShootout($team1, $team2): array
    {
        $team1Score = 0;
        $team2Score = 0;

        for ($round = 1; $round <= 5; $round++) {
            $remainingShots = 6 - $round;

            $team2Score += $this->takePenaltyShot($team2, $team1);
            
            if (abs($team1Score - $team2Score) > $remainingShots) {
                break;
            }

            $team1Score += $this->takePenaltyShot($team1, $team2);
            
            if (abs($team1Score - $team2Score) > ($remainingShots - 1)) {
                break;
            }
        }

        while ($team1Score === $team2Score) {
            $team2Score += $this->takePenaltyShot($team2, $team1);
            $team1Score += $this->takePenaltyShot($team1, $team2);
        }

        return [
            'team1_penalty_score' => $team1Score,
            'team2_penalty_score' => $team2Score,
            'winner' => $team1Score > $team2Score ? 1 : 2,
        ];
    }

    protected function takePenaltyShot($attacking, $defending): int
    {
        $attackPower = ($attacking->attack ?? 50) + (($attacking->mental ?? 50) * 0.1);
        $defensePower = ($defending->defense ?? 50) + (($defending->mental ?? 50) * 0.1);

        $successChance = $attackPower / ($attackPower + $defensePower);

        return rand(1, 100) <= ($successChance * 100) ? 1 : 0;
    }
}
